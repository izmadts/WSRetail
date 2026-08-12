<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\User;
use App\Models\Product;
use App\Models\Expense;
use App\Services\SaleService;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
    protected $saleService;
    protected $commissionService;

    public function __construct(SaleService $saleService, CommissionService $commissionService)
    {
        $this->saleService = $saleService;
        $this->commissionService = $commissionService;
    }

    public function index()
    {
        $sales = Sale::with('customer', 'agent', 'createdBy')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.sales.index', compact('sales'));
    }

    public function create()
    {
        $customers = Customer::active()->with('customerGroup')->orderBy('name')->get();
        $agents = User::where('role', 'sales_agent')
            ->where('is_active', true)
            ->whereNotNull('approved_at')
            ->orderBy('name')
            ->get(['id', 'name']);
        $products = Product::active()->where('current_stock', '>', 0)->orderBy('name')->get();
        $commissionPreview = $this->commissionPreviewData($agents);
        $productsForJs = $this->productsForJs($products);
        return view('admin.sales.create', compact('customers', 'agents', 'products', 'commissionPreview', 'productsForJs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'agent_id' => 'nullable|exists:users,id',
            'sale_date' => 'required|date',
            'payment_term' => 'required|in:cash,credit',
            'status' => 'required|in:draft,confirmed',
            'amount_received' => 'nullable|numeric|min:0',
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
            $this->storeSale($validated);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            // Catches SaleService's defensive throws (insufficient stock, a
            // missing chart-of-accounts entry) - without this they surfaced
            // as a raw 500 error page instead of telling the admin what
            // actually went wrong. DB::transaction() below still rolls back
            // correctly regardless of what happens to the exception here.
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('admin.sales.index')
            ->with('success', 'Sale created successfully! Stock and accounting updated.');
    }

    private function storeSale(array $validated)
    {
        DB::transaction(function () use ($validated) {
            $customer = Customer::find($validated['customer_id']);
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

            $amountReceived = (float) ($validated['amount_received'] ?? 0);
            if ($amountReceived > $totalAmount) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'amount_received' => 'Amount received cannot exceed the sale total.',
                ]);
            }

            // A 'cash' sale posts its FULL total straight to the Cash account
            // the moment it's confirmed (SaleService::postAccounting) - there
            // is no receivable behind a cash sale to collect the rest from
            // later. Confirming one for less than the full total would
            // overstate Cash by the shortfall with nothing tracking the
            // difference. If the customer isn't paying it all today, this
            // has to be a Credit sale instead.
            if ($validated['status'] !== 'draft' && $validated['payment_term'] === 'cash' && abs($amountReceived - $totalAmount) > 0.01) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'amount_received' => 'A cash sale must be paid in full. Enter the full amount received, save as Draft instead, or choose Credit payment term if the customer will pay over time.',
                ]);
            }

            // A real payment can't be received against a draft/quote - if
            // money changed hands, the sale is confirmed, regardless of what
            // the form's status field happened to submit. status only ever
            // reaches 'paid'/'partial' via recordPayment() below, never by
            // being written directly here - that's what let a sale be
            // labeled "Paid" with $0 actually recorded.
            $status = $amountReceived > 0 ? 'confirmed' : $validated['status'];

            // Credit-hold / credit-limit gate - both off by default, admin
            // opt-in via Settings > Commission & Bonus. A draft sale hasn't
            // posted a receivable yet, so it's not gated here.
            if ($status !== 'draft' && $validated['payment_term'] === 'credit') {
                $blockMessage = $this->commissionService->creditGateMessage($customer, $totalAmount);
                if ($blockMessage) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'customer_id' => $blockMessage,
                    ]);
                }
            }

            // Commission is calculated by CommissionService below (settings-
            // driven progressive tiers for cash, per-payment accrual for
            // credit) - not a flat agent rate stored as a lump amount here.
            $sale = Sale::create([
                'customer_id' => $validated['customer_id'],
                'agent_id' => $validated['agent_id'] ?? null,
                'sale_date' => $validated['sale_date'],
                'payment_term' => $validated['payment_term'],
                'status' => $status,
                'sub_total' => $subTotal,
                'discount' => $validated['discount'] ?? 0,
                'discount_type' => $validated['discount_type'] ?? 'fixed',
                'tax' => $validated['tax'] ?? 0,
                'shipping_cost' => $validated['shipping_cost'] ?? 0,
                'total_amount' => $totalAmount,
                'commission_amount' => 0,
                'paid_amount' => 0,
                'due_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($itemsData as $itemData) {
                $sale->items()->create($itemData);
            }

            // Once per sale, not once per line item.
            $customer->incrementOrderCount();
            $this->commissionService->awardNewCustomerBonus($customer, $sale);

            $this->saleService->applyStockAndAccounting($sale);

            if ($amountReceived > 0) {
                // recordPayment() sets paid_amount/due_amount/status itself
                // (it flips to 'paid' once due_amount reaches 0, 'partial'
                // otherwise) - routing an instant full payment through here
                // (instead of creating the row already status='paid') keeps
                // the payment trail/accounting consistent for a
                // pay-in-full-at-checkout sale.
                $this->saleService->recordPayment($sale, $amountReceived, 'cash', $validated['sale_date']);
            }
        });
    }

    /**
     * ✅ ADD THIS METHOD - Display sale details
     */
    public function show(Sale $sale)
    {
        $sale->load('customer', 'agent', 'items.product', 'payments', 'createdBy');
        return view('admin.sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        if ($sale->status == 'paid') {
            return back()->with('error', 'Cannot edit a paid sale!');
        }

        $customers = Customer::active()->with('customerGroup')->orderBy('name')->get();
        $agents = User::where('role', 'sales_agent')
            ->where('is_active', true)
            ->whereNotNull('approved_at')
            ->orderBy('name')
            ->get(['id', 'name']);
        $sale->load('items', 'customer.customerGroup');

        // Union "currently sellable" products with whatever this sale's
        // existing items already reference, so an item on a product that's
        // since gone inactive/out-of-stock still shows correctly instead of
        // the edit form silently blanking its selection.
        $existingProductIds = $sale->items->pluck('product_id');
        $products = Product::where(function ($q) {
                $q->where('is_active', true)->where('current_stock', '>', 0);
            })
            ->orWhereIn('id', $existingProductIds)
            ->orderBy('name')
            ->get();

        $commissionPreview = $this->commissionPreviewData($agents, $sale->id);
        $productsForJs = $this->productsForJs($products);
        return view('admin.sales.edit', compact('sale', 'customers', 'agents', 'products', 'commissionPreview', 'productsForJs'));
    }

    /**
     * Cash-tier table, credit rate, and each agent's month-to-date confirmed
     * cash sales - everything the create/edit form's JS needs to preview the
     * real settings-driven commission (CommissionService::calculateCashCommission)
     * instead of a flat rate that hasn't matched the actual engine since the
     * Phase 3 rebuild. $excludeSaleId keeps an in-progress edit from double
     * counting the sale's own prior amount into its own bracket.
     */
    private function commissionPreviewData($agents, $excludeSaleId = null)
    {
        $now = now();
        $mtdByAgent = [];

        foreach ($agents as $agent) {
            $query = Sale::where('agent_id', $agent->id)
                ->where('payment_term', 'cash')
                ->whereIn('status', ['confirmed', 'partial', 'paid'])
                ->whereMonth('sale_date', $now->month)
                ->whereYear('sale_date', $now->year);

            if ($excludeSaleId) {
                $query->where('id', '!=', $excludeSaleId);
            }

            $mtdByAgent[$agent->id] = (float) $query->sum('total_amount');
        }

        return [
            'cash_tiers' => CommissionService::getSetting('commission.cash_tiers'),
            'credit_rate' => (float) CommissionService::getSetting('commission.credit_rate'),
            'agent_mtd_cash' => $mtdByAgent,
        ];
    }

    /**
     * Flat product data for the sale form's Alpine component - it filters
     * this client-side by is_retail/is_wholesale to match the selected
     * customer's group, and reads sale_price/wholesale_price to auto-fill
     * the right price for that group instead of always defaulting to retail.
     */
    private function productsForJs($products)
    {
        return $products->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'code' => $p->code,
            'sale_price' => (float) $p->sale_price,
            'wholesale_price' => (float) $p->wholesale_price,
            'purchase_price' => (float) $p->purchase_price,
            'current_stock' => (float) $p->current_stock,
            'is_retail' => (bool) $p->is_retail,
            'is_wholesale' => (bool) $p->is_wholesale,
        ])->values();
    }

    public function update(Request $request, Sale $sale)
    {
        if ($sale->status == 'paid') {
            return back()->with('error', 'Cannot update a paid sale!');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'agent_id' => 'nullable|exists:users,id',
            'sale_date' => 'required|date',
            'payment_term' => 'required|in:cash,credit',
            // 'partial'/'paid' deliberately excluded - those are derived
            // from recorded payments (SaleService::recordPayment), never
            // picked directly, or a sale could land on 'paid' with $0 paid.
            'status' => 'required|in:draft,confirmed',
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

        // Same rule as creation: a 'cash' sale posts its FULL total straight
        // to Cash with nothing tracking any shortfall. This form doesn't
        // collect a payment, so a sale that still owes money can't be
        // (re)labeled 'cash' here - Add Payment is the only real way to
        // settle it, or Credit is the correct term for it either way.
        if ($validated['status'] !== 'draft' && $validated['payment_term'] === 'cash' && (float) $sale->due_amount > 0.01) {
            return back()->with('error', 'This sale still has an outstanding balance, so it cannot be set to Cash. Use Credit instead, or record the remaining payment first via Add Payment.');
        }

        try {
            DB::transaction(function () use ($validated, $sale) {
                $itemsData = [];

                foreach ($validated['items'] as $item) {
                    $itemTotal = $item['quantity'] * $item['unit_price'];
                    $itemDiscount = $item['discount'] ?? 0;
                    $itemTax = $item['tax'] ?? 0;
                    $totalPrice = $itemTotal - $itemDiscount + $itemTax;

                    $itemsData[] = [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount' => $item['discount'] ?? 0,
                        'tax' => $item['tax'] ?? 0,
                        'total_price' => $totalPrice,
                    ];
                }

                $sale->update([
                    'customer_id' => $validated['customer_id'],
                    'agent_id' => $validated['agent_id'] ?? null,
                    'sale_date' => $validated['sale_date'],
                    'payment_term' => $validated['payment_term'],
                    'status' => $validated['status'],
                    'discount' => $validated['discount'] ?? 0,
                    'discount_type' => $validated['discount_type'] ?? 'fixed',
                    'tax' => $validated['tax'] ?? 0,
                    'shipping_cost' => $validated['shipping_cost'] ?? 0,
                    'notes' => $validated['notes'] ?? null,
                ]);

                // Reverses old stock/accounting, syncs items, re-applies fresh
                // stock/accounting (also recalculates sub_total/total_amount/
                // due_amount from the new items via Sale::calculateTotals()).
                $this->saleService->syncItemsAndUpdate($sale, $itemsData);
            });
        } catch (\Exception $e) {
            // Catches SaleService's defensive throws (insufficient stock, a
            // missing chart-of-accounts entry) - without this they surfaced
            // as a raw 500 error page instead of telling the admin what
            // actually went wrong.
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('admin.sales.index')
            ->with('success', 'Sale updated successfully! Stock and accounting adjusted.');
    }

    public function destroy(Sale $sale)
    {
        if ($sale->status == 'paid') {
            return back()->with('error', 'Cannot delete a paid sale!');
        }

        try {
            DB::transaction(function () use ($sale) {
                $this->saleService->reverseForDeletion($sale);
                $sale->delete();
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.sales.index')
            ->with('success', 'Sale deleted successfully! Stock and accounting reversed.');
    }

    /**
     * Commits a still-draft sale - flips it to confirmed and runs
     * SaleService::applyStockAndAccounting (a draft sale has no stock/ledger
     * effect yet, see the status gate at the top of that method). Primarily
     * for customer-placed orders with no agent (source=customer_app,
     * agent_id null - "direct" orders per the customer API), which only an
     * admin can act on since no agent owns them, but works for any draft.
     */
    public function confirm(Sale $sale)
    {
        if ($sale->status !== 'draft') {
            return back()->with('error', 'Only a draft sale can be confirmed.');
        }

        $sale->status = 'confirmed';
        $sale->save();

        try {
            $this->saleService->applyStockAndAccounting($sale);
        } catch (\Exception $e) {
            // Most likely: stock this draft reserved got sold elsewhere in
            // the meantime. Roll the status change back so the sale stays a
            // confirmable draft instead of getting stuck 'confirmed' with no
            // stock/ledger effect behind it.
            $sale->status = 'draft';
            $sale->saveQuietly();
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Order confirmed - stock and accounting updated.');
    }

    /**
     * No reversal needed - a draft sale never had stock or ledger entries
     * posted in the first place.
     */
    public function reject(Sale $sale)
    {
        if ($sale->status !== 'draft') {
            return back()->with('error', 'Only a draft sale can be rejected.');
        }

        $sale->update(['status' => 'cancelled']);

        return back()->with('success', 'Order rejected.');
    }

    public function addPayment(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $sale->due_amount,
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,credit_card',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'bank_service_charge' => 'nullable|numeric|min:0',
        ]);

        try {
            // Single call: creates the payment row, updates paid/due/recovery%/
            // status, and posts the payment journal entry for credit sales - on
            // every payment, not just the one that finally reaches $0 due. A
            // credit sale paid off in 3 installments needs all 3 to hit the
            // ledger, not just the last one.
            $this->saleService->recordPayment(
                $sale,
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
                "Bank charge for payment on Sale #{$sale->invoice_no}"
            );
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payment added successfully!');
    }
}