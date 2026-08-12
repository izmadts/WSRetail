<?php

namespace App\Http\Controllers\Agent;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\Customer;
use App\Models\Product;
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
        $sales = Sale::where('agent_id', Auth::id())
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('agent.sales.index', compact('sales'));
    }

    public function create()
    {
        $customers = Customer::where('created_by_agent_id', Auth::id())
            ->active()
            ->with('customerGroup')
            ->orderBy('name')
            ->get();

        $products = Product::active()
            ->where('current_stock', '>', 0)
            ->orderBy('name')
            ->get();
        $agents = User::where('role', 'sales_agent')->get();
        $productsForJs = $this->productsForJs($products);
        return view('agent.sales.create', compact('customers', 'products', 'agents', 'productsForJs'));
    }

    /**
     * Flat product data for the sale form's Alpine component - filtered
     * client-side by is_retail/is_wholesale to match the selected
     * customer's group, and used to auto-fill the right price for that
     * group instead of always defaulting to retail.
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
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
            // as a raw 500 error page instead of telling the agent what
            // actually went wrong.
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('agent.sales.index')
            ->with('success', 'Sale created successfully!');
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

            // A real payment can't be received against a draft/quote - the
            // sale is confirmed the moment money changes hands. status only
            // ever reaches 'paid'/'partial' via recordPayment() below, never
            // written directly, so it can't land on "Paid" with $0 recorded.
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

            $sale = Sale::create([
                'customer_id' => $validated['customer_id'],
                'agent_id' => Auth::id(),
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
                'commission_due_amount' => 0,
                'paid_amount' => 0,
                'due_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($itemsData as $itemData) {
                $sale->items()->create($itemData);
            }

            // Routed through SaleService instead of mutating stock inline:
            // this adds the availability check the admin sale flow already
            // has (agent sales could previously oversell into negative
            // stock) and actually posts journal entries, which agent sales
            // never did before. It also calculates and logs cash-sale
            // commission itself now (settings-driven progressive tiers),
            // so there's no separate commission block here anymore.
            $this->saleService->applyStockAndAccounting($sale);

            // Update customer order count and check the new-customer bonus
            // (fixed, admin-configurable amount - the service itself checks
            // the order-count threshold and activity, and won't double-award).
            $customer->incrementOrderCount();
            $this->commissionService->awardNewCustomerBonus($customer, $sale);

            if ($amountReceived > 0) {
                // recordPayment() sets paid_amount/due_amount/status itself
                // and posts the Receivable->Cash conversion for credit sales
                // - routing an instant full payment through here (instead of
                // creating the row already status='paid') keeps the payment
                // trail/accounting consistent.
                $this->saleService->recordPayment($sale, $amountReceived, 'cash', $validated['sale_date']);
            }
        });
    }

    public function show(Sale $sale)
    {
        if ($sale->agent_id != Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        $sale->load('customer', 'items.product', 'payments');
        return view('agent.sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        if ($sale->agent_id != Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        if ($sale->status == 'paid') {
            return back()->with('error', 'Cannot edit a paid sale!');
        }

        $customers = Customer::where('created_by_agent_id', Auth::id())->active()->with('customerGroup')->orderBy('name')->get();
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

        $productsForJs = $this->productsForJs($products);
        return view('agent.sales.edit', compact('sale', 'customers', 'products', 'productsForJs'));
    }

    public function update(Request $request, Sale $sale)
    {
        if ($sale->agent_id != Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        if ($sale->status == 'paid') {
            return back()->with('error', 'Cannot update a paid sale!');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'sale_date' => 'required|date',
            'payment_term' => 'required|in:cash,credit',
            // 'partial'/'paid' deliberately excluded - derived from recorded
            // payments (SaleService::recordPayment), never picked directly.
            'status' => 'required|in:draft,confirmed',
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

        // Agents may only reassign a sale to their own customers.
        $customer = Customer::where('id', $validated['customer_id'])
            ->where('created_by_agent_id', Auth::id())
            ->first();
        if (!$customer) {
            abort(403, 'Unauthorized access.');
        }

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
                    'sale_date' => $validated['sale_date'],
                    'payment_term' => $validated['payment_term'],
                    'status' => $validated['status'],
                    'discount' => $validated['discount'] ?? 0,
                    'discount_type' => $validated['discount_type'] ?? 'fixed',
                    'tax' => $validated['tax'] ?? 0,
                    'shipping_cost' => $validated['shipping_cost'] ?? 0,
                    'notes' => $validated['notes'] ?? null,
                ]);

                $this->saleService->syncItemsAndUpdate($sale, $itemsData);
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('agent.sales.index')
            ->with('success', 'Sale updated successfully!');
    }

    public function destroy(Sale $sale)
    {
        if ($sale->agent_id != Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

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

        return redirect()->route('agent.sales.index')
            ->with('success', 'Sale deleted successfully!');
    }

    /**
     * Commits a still-draft sale owned by this agent - flips it to confirmed
     * and runs SaleService::applyStockAndAccounting, which is what actually
     * deducts stock and posts ledger entries (a draft sale has done neither
     * yet). This is how a customer's order placed through this agent
     * (source=customer_app, created via the customer API's
     * OrderController::store) gets turned into a real sale.
     */
    public function confirm(Sale $sale)
    {
        if ($sale->agent_id != Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

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
        if ($sale->agent_id != Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        if ($sale->status !== 'draft') {
            return back()->with('error', 'Only a draft sale can be rejected.');
        }

        $sale->update(['status' => 'cancelled']);

        return back()->with('success', 'Order rejected.');
    }

    public function addPayment(Request $request, Sale $sale)
    {
        if ($sale->agent_id != Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $sale->due_amount,
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,credit_card',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        try {
            // Creates the payment row, updates paid/due/recovery%/status, posts
            // the payment journal entry, accrues credit-sale commission on this
            // payment (not held until fully settled), and checks the recovery
            // bonus - all in one call, same as the admin controller. Doing that
            // commission/bonus logic here too would double it, since
            // SaleService::recordPayment() -> CommissionService now handles it.
            $this->saleService->recordPayment(
                $sale,
                $validated['amount'],
                $validated['payment_method'],
                $validated['payment_date'],
                $validated['reference_no'] ?? null,
                $validated['notes'] ?? null
            );
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payment added successfully!');
    }
}