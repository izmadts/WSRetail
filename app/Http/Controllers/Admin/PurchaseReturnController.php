<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\PurchaseItem;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PurchaseReturnController extends Controller
{
    public function index()
    {
        $returns = PurchaseReturn::with('supplier', 'purchase', 'createdBy')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.purchase-returns.index', compact('returns'));
    }

    public function create()
    {
        // Matches Purchase::scopeReceived() - 'partial' was previously
        // missing, so a partially-paid purchase (real stock/payable already
        // posted) couldn't be picked for a return through this screen.
        $purchases = Purchase::whereIn('status', ['received', 'partial', 'paid'])
            ->with('supplier')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.purchase-returns.create', compact('purchases'));
    }

    public function getPurchaseDetails($purchaseId)
    {
        $purchase = Purchase::with('items.product', 'supplier')->findOrFail($purchaseId);
        return response()->json($purchase);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'return_date' => 'required|date',
            'reason' => 'nullable|string',
            'refund_method' => 'required|in:cash,credit,cheque,bank_transfer',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_item_id' => 'required|exists:purchase_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0',
        ]);

        // Guard: never let a return exceed what was actually purchased
        // (minus anything already returned against that same line item).
        foreach ($validated['items'] as $item) {
            $purchaseItem = PurchaseItem::find($item['purchase_item_id']);
            // whereHas('purchaseReturn') respects PurchaseReturn's soft-delete
            // scope - without it, a deleted return's items kept permanently
            // counting as "already returned" even though its stock/ledger
            // effect had been fully undone.
            $alreadyReturned = PurchaseReturnItem::where('purchase_item_id', $item['purchase_item_id'])
                ->whereHas('purchaseReturn')
                ->sum('quantity');
            $availableToReturn = $purchaseItem->quantity - $alreadyReturned;

            if ($item['quantity'] > $availableToReturn) {
                throw ValidationException::withMessages([
                    'items' => "Cannot return {$item['quantity']} of \"{$purchaseItem->product->name}\" - only {$availableToReturn} available to return.",
                ]);
            }
        }

        try {
            DB::transaction(function () use ($validated) {
            $purchase = Purchase::find($validated['purchase_id']);

            // Calculate totals
            $subTotal = 0;
            $totalDiscount = 0;
            $totalTax = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $itemDiscount = $item['discount'] ?? 0;
                $itemTax = $item['tax'] ?? 0;
                $totalPrice = $itemTotal - $itemDiscount + $itemTax;

                $subTotal += $totalPrice;
                $totalDiscount += $itemDiscount;
                $totalTax += $itemTax;
                $itemsData[] = $item;
            }

            $totalAmount = $subTotal - $totalDiscount + $totalTax;

            // Create purchase return
            $purchaseReturn = PurchaseReturn::create([
                'purchase_id' => $validated['purchase_id'],
                'supplier_id' => $purchase->supplier_id,
                'return_date' => $validated['return_date'],
                'reason' => $validated['reason'] ?? null,
                'sub_total' => $subTotal,
                'discount' => $totalDiscount,
                'tax' => $totalTax,
                'total_amount' => $totalAmount,
                'refund_method' => $validated['refund_method'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Create return items
            foreach ($itemsData as $itemData) {
                $purchaseReturn->items()->create([
                    'purchase_item_id' => $itemData['purchase_item_id'],
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount' => $itemData['discount'] ?? 0,
                    'tax' => $itemData['tax'] ?? 0,
                    'total_price' => $itemData['total_price'],
                ]);
            }

            // Items now exist, so it's safe to apply stock + accounting.
            $purchaseReturn->refresh();
            $purchaseReturn->updateStockAndAccounts();
            });
        } catch (\Exception $e) {
            // Catches PurchaseReturn's defensive throws (a missing
            // chart-of-accounts entry, etc.) - without this they surfaced as
            // a raw 500 error page instead of telling the admin what
            // actually went wrong.
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('admin.purchase-returns.index')
            ->with('success', 'Purchase return created successfully!');
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load('supplier', 'purchase', 'items.product', 'createdBy');
        return view('admin.purchase-returns.show', compact('purchaseReturn'));
    }

    public function destroy(PurchaseReturn $purchaseReturn)
    {
        try {
            // Model's deleting event handles reversing stock + accounting
            // (this event now actually exists - see PurchaseReturn::boot()).
            $purchaseReturn->delete();
        } catch (\Exception $e) {
            // Most likely: the returned-out stock has since been sold or
            // moved elsewhere, so undoing this return would take it negative.
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.purchase-returns.index')
            ->with('success', 'Purchase return deleted successfully!');
    }
}
