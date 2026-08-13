<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SalesReturnController extends Controller
{
    public function index()
    {
        $returns = SalesReturn::with('customer', 'sale', 'createdBy')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.sales-returns.index', compact('returns'));
    }

    public function create()
    {
        // A confirmed (unpaid credit) sale already has real stock/revenue
        // posted and is just as legitimate a target for a return as a paid
        // or partially-paid one - excluding it just forced a workaround.
        $sales = Sale::whereIn('status', ['confirmed', 'partial', 'paid'])
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.sales-returns.create', compact('sales'));
    }

    public function getSaleDetails($saleId)
    {
        $sale = Sale::with('items.product', 'customer')->findOrFail($saleId);
        return response()->json($sale);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'return_date' => 'required|date',
            'reason' => 'nullable|string',
            'refund_method' => 'required|in:cash,credit,cheque,bank_transfer',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.sale_item_id' => 'required|exists:sale_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0',
        ]);

        // A sale item can't be returned more than was actually sold, once
        // you account for whatever's already been returned against it.
        // whereHas('salesReturn') matters here: SalesReturnItem rows aren't
        // soft-deleted themselves, only their parent SalesReturn is, so a
        // plain where() would still count quantity from a deleted (reversed)
        // return.
        foreach ($validated['items'] as $item) {
            $saleItem = SaleItem::find($item['sale_item_id']);
            $alreadyReturned = SalesReturnItem::where('sale_item_id', $item['sale_item_id'])
                ->whereHas('salesReturn')
                ->sum('quantity');
            $available = $saleItem->quantity - $alreadyReturned;

            if ($item['quantity'] > $available) {
                throw ValidationException::withMessages([
                    'items' => "Cannot return {$item['quantity']} of \"{$saleItem->product->name}\" - only {$available} remaining available to return.",
                ]);
            }
        }

        try {
            DB::transaction(function () use ($validated) {
            $sale = Sale::find($validated['sale_id']);

            // sub_total is the gross pre-discount/tax total (matching Sale's
            // own convention) - discount/tax are applied once, on top of it,
            // not baked into sub_total AND applied again.
            $subTotal = 0;
            $totalDiscount = 0;
            $totalTax = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $itemDiscount = $item['discount'] ?? 0;
                $itemTax = $item['tax'] ?? 0;

                $subTotal += $itemTotal;
                $totalDiscount += $itemDiscount;
                $totalTax += $itemTax;
                $itemsData[] = $item;
            }

            $totalAmount = $subTotal - $totalDiscount + $totalTax;

            $salesReturn = SalesReturn::create([
                'sale_id' => $validated['sale_id'],
                'customer_id' => $sale->customer_id,
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

            foreach ($itemsData as $itemData) {
                $salesReturn->items()->create([
                    'sale_item_id' => $itemData['sale_item_id'],
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount' => $itemData['discount'] ?? 0,
                    'tax' => $itemData['tax'] ?? 0,
                    'total_price' => $itemData['total_price'],
                ]);
            }

            // Items now exist - safe to reverse stock/accounting.
            // (This used to run in a model created() hook that fired before
            // any items existed, making it a silent no-op.)
            $salesReturn->load('items.product');
            $salesReturn->reverseStockAndAccounting();
            });
        } catch (\Exception $e) {
            // Catches SalesReturn's defensive throws (a missing
            // chart-of-accounts entry, etc.) - without this they surfaced as
            // a raw 500 error page instead of telling the admin what
            // actually went wrong.
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('admin.sales-returns.index')
            ->with('success', 'Sales return created successfully!');
    }

    public function show(SalesReturn $salesReturn)
    {
        $salesReturn->load('customer', 'sale', 'items.product', 'createdBy');
        return view('admin.sales-returns.show', compact('salesReturn'));
    }

    public function destroy(SalesReturn $salesReturn)
    {
        try {
            // Model's deleting event will handle reversing
            $salesReturn->delete();
        } catch (\Exception $e) {
            // Most likely: some of the returned stock has since been sold or
            // moved elsewhere, so undoing this return would take it negative.
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.sales-returns.index')
            ->with('success', 'Sales return deleted successfully!');
    }
}
