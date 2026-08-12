<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Account;
use App\Models\Expense;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    protected $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    public function index()
    {
        $purchases = Purchase::with('supplier', 'createdBy')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get();
        return view('admin.purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'payment_term' => 'required|in:cash,credit',
            'status' => 'required|in:draft,ordered,received,paid',
            'sub_total' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
            'tax' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $subTotal = 0;
                $itemsData = [];

                foreach ($validated['items'] as $item) {
                    $itemTotal = $item['quantity'] * $item['unit_price'];
                    $itemDiscount = $item['discount'] ?? 0;
                    $itemTax = $item['tax'] ?? 0;
                    $totalPrice = $itemTotal - $itemDiscount + $itemTax;

                    $subTotal += $totalPrice;
                    $itemsData[] = [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount' => $item['discount'] ?? 0,
                        'tax' => $item['tax'] ?? 0,
                        'total_price' => $totalPrice,
                    ];
                }

                $discountAmount = $validated['discount_type'] == 'percentage'
                    ? ($subTotal * ($validated['discount'] ?? 0) / 100)
                    : ($validated['discount'] ?? 0);

                $totalAmount = $subTotal - $discountAmount + ($validated['tax'] ?? 0) + ($validated['shipping_cost'] ?? 0);

                // Always start at 0 paid. recordPayment() below is the ONLY place
                // that increments paid_amount, so it can never be double-counted.
                $purchase = Purchase::create([
                    'supplier_id' => $validated['supplier_id'],
                    'purchase_date' => $validated['purchase_date'],
                    'payment_term' => $validated['payment_term'],
                    'status' => $validated['status'],
                    'sub_total' => $subTotal,
                    'discount' => $validated['discount'] ?? 0,
                    'discount_type' => $validated['discount_type'] ?? 'fixed',
                    'tax' => $validated['tax'] ?? 0,
                    'shipping_cost' => $validated['shipping_cost'] ?? 0,
                    'total_amount' => $totalAmount,
                    'paid_amount' => 0,
                    'due_amount' => $totalAmount,
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => Auth::id(),
                ]);

                foreach ($itemsData as $itemData) {
                    $purchase->items()->create($itemData);
                }

                // Apply stock + inventory/payable-or-cash accounting (idempotent)
                $this->purchaseService->applyStockAndAccounting($purchase);

                // If created directly as "paid", record the settling payment.
                if ($validated['status'] == 'paid') {
                    $isCash = $validated['payment_term'] == 'cash';
                    $this->purchaseService->recordPayment(
                        $purchase,
                        $totalAmount,
                        $isCash ? 'cash' : 'bank_transfer',
                        $validated['purchase_date'],
                        null,
                        'Full payment at purchase creation',
                        // Cash purchases already credited Cash directly above,
                        // so skip re-posting the cash journal entry here.
                        !$isCash
                    );
                }
            });
        } catch (\Exception $e) {
            // Catches PurchaseService's defensive throws (a missing
            // chart-of-accounts entry, etc.) - without this they surfaced as
            // a raw 500 error page instead of telling the admin what
            // actually went wrong.
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('admin.purchases.index')
            ->with('success', 'Purchase created successfully! Stock and accounting updated.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('supplier', 'items.product', 'payments', 'createdBy');

        // Available cash/bank - used to warn (not block) if a payment would
        // take the paying account negative. See Account::getBalanceAttribute().
        $cashBalance = Account::where('code', '1010')->first()->balance ?? 0;
        $bankBalance = Account::where('code', '1020')->first()->balance ?? 0;

        return view('admin.purchases.show', compact('purchase', 'cashBalance', 'bankBalance'));
    }

    public function edit(Purchase $purchase)
    {
        if ($purchase->status == 'paid') {
            return back()->with('error', 'Cannot edit a paid purchase!');
        }

        $suppliers = Supplier::active()->orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get();
        $purchase->load('items');
        return view('admin.purchases.edit', compact('purchase', 'suppliers', 'products'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        if ($purchase->status == 'paid') {
            return back()->with('error', 'Cannot update a paid purchase!');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'payment_term' => 'required|in:cash,credit',
            'status' => 'required|in:draft,ordered,received,paid',
            'sub_total' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
            'tax' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated, $purchase) {
                $subTotal = 0;
                $itemsData = [];

                foreach ($validated['items'] as $item) {
                    $itemTotal = $item['quantity'] * $item['unit_price'];
                    $itemDiscount = $item['discount'] ?? 0;
                    $itemTax = $item['tax'] ?? 0;
                    $totalPrice = $itemTotal - $itemDiscount + $itemTax;

                    $subTotal += $totalPrice;
                    $itemsData[] = [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount' => $item['discount'] ?? 0,
                        'tax' => $item['tax'] ?? 0,
                        'total_price' => $totalPrice,
                    ];
                }

                $purchase->update([
                    'supplier_id' => $validated['supplier_id'],
                    'purchase_date' => $validated['purchase_date'],
                    'payment_term' => $validated['payment_term'],
                    'status' => $validated['status'],
                    'sub_total' => $subTotal,
                    'discount' => $validated['discount'] ?? 0,
                    'discount_type' => $validated['discount_type'] ?? 'fixed',
                    'tax' => $validated['tax'] ?? 0,
                    'shipping_cost' => $validated['shipping_cost'] ?? 0,
                    'notes' => $validated['notes'] ?? null,
                ]);

                // Reverses old stock/accounting, syncs items, re-applies fresh
                // stock/accounting, and auto-settles payment if now marked paid.
                $this->purchaseService->syncItemsAndUpdate($purchase, $itemsData);
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('admin.purchases.index')
            ->with('success', 'Purchase updated successfully! Stock and accounting adjusted.');
    }

    public function destroy(Purchase $purchase)
    {
        if ($purchase->status == 'paid') {
            return back()->with('error', 'Cannot delete a paid purchase!');
        }

        try {
            DB::transaction(function () use ($purchase) {
                $this->purchaseService->reverseStockAndAccounting($purchase);
                $this->purchaseService->reversePaymentsAndAccounting($purchase);
                $purchase->delete();
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.purchases.index')
            ->with('success', 'Purchase deleted successfully! Stock, payments, and accounting reversed.');
    }

    public function addPayment(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $purchase->due_amount,
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,credit_card',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'bank_service_charge' => 'nullable|numeric|min:0',
        ]);

        try {
            // Single call: creates the payment row, updates paid/due/status,
            // and posts the payment journal entry. No manual duplication here.
            $this->purchaseService->recordPayment(
                $purchase,
                $validated['amount'],
                $validated['payment_method'],
                $validated['payment_date'],
                $validated['reference_no'] ?? null,
                $validated['notes'] ?? null
            );

            Expense::recordBankServiceCharge(
                $validated['bank_service_charge'] ?? 0,
                $validated['payment_date'],
                $validated['payment_method'],
                "Bank charge for payment on Purchase #{$purchase->invoice_no}"
            );
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payment added successfully!');
    }
}
