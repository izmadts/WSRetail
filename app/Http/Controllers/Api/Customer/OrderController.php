<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Sale;
use App\Models\Product;
use App\Http\Resources\Api\SaleResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends ApiController
{
    /**
     * Orders placed here are plain Sale rows (source=customer_app,
     * status=draft) - the same model/table/reports agents and admin
     * already use, not a separate "order" concept. Nothing is deducted or
     * posted to the ledger at this point: SaleService::applyStockAndAccounting
     * no-ops for any status other than confirmed/paid, so a draft sale has
     * zero stock/accounting effect until an agent or admin confirms it
     * (POST /agent/sales/{id}/confirm or the admin equivalent).
     */
    public function index(Request $request)
    {
        $query = Sale::where('customer_id', $this->customer()->id)
            ->with('items.product')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sales = $query->paginate($request->input('per_page', 20));

        return $this->paginated($sales, fn ($s) => new SaleResource($s));
    }

    public function show($id)
    {
        $sale = Sale::where('customer_id', $this->customer()->id)
            ->with('items.product', 'payments')
            ->find($id);

        if (!$sale) {
            return $this->error('Order not found.', 404);
        }

        return $this->success(new SaleResource($sale));
    }

    /**
     * The customer (seller-app/Mandi) API is a wholesale-only channel,
     * full stop - not conditional on customer_group. A customer's
     * customer_group still exists and still defaults to Wholesale at
     * /connect (useful for reporting/reconciliation elsewhere in WSERP),
     * but this endpoint set never consults it: every order here prices and
     * filters against wholesale_price/is_wholesale, regardless of what an
     * admin later sets that field to for other purposes. created_by_agent_id
     * is unrelated to any of this - it only decides commission credit.
     */
    private function resolveChannel()
    {
        $customer = $this->customer();

        return [
            'agent_id' => $customer->created_by_agent_id,
            'price_field' => 'wholesale_price',
            'availability_flag' => 'is_wholesale',
            'label' => 'wholesale',
        ];
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sale_date' => 'required|date',
            'payment_term' => 'required|in:cash,credit',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $validated = $validator->validated();
        $channel = $this->resolveChannel();
        $customer = $this->customer();

        // Price is resolved server-side from the product record, never
        // trusted from the app - this endpoint is reachable by any
        // connected seller, unlike the agent API's internal client.
        $itemsData = [];
        $subTotal = 0;

        foreach ($validated['items'] as $line) {
            $product = Product::find($line['product_id']);

            if (!$product || !$product->is_active) {
                return $this->error("Product #{$line['product_id']} is not available.", 422);
            }

            if (!$product->{$channel['availability_flag']}) {
                return $this->error("\"{$product->name}\" is not available for {$channel['label']} orders.", 422);
            }

            if ((float) $line['quantity'] > (float) $product->current_stock) {
                return $this->error("Only {$product->current_stock} of \"{$product->name}\" is currently in stock.", 422);
            }

            $unitPrice = (float) ($channel['price_field'] === 'wholesale_price' ? $product->wholesale_price : $product->sale_price);
            $totalPrice = round($unitPrice * (float) $line['quantity'], 2);
            $subTotal += $totalPrice;

            $itemsData[] = [
                'product_id' => $product->id,
                'quantity' => $line['quantity'],
                'unit_price' => $unitPrice,
                'discount' => 0,
                'tax' => 0,
                'total_price' => $totalPrice,
            ];
        }

        $sale = DB::transaction(function () use ($customer, $channel, $validated, $itemsData, $subTotal) {
            $sale = Sale::create([
                'customer_id' => $customer->id,
                'agent_id' => $channel['agent_id'],
                'source' => 'customer_app',
                'sale_date' => $validated['sale_date'],
                // Whatever the customer picked at checkout (Cash on
                // Delivery vs credit/account) - recordPayment(), called by
                // whoever confirms/collects, is what actually moves this
                // toward partial/paid; picking "cash" here does not imply
                // payment has already happened.
                'payment_term' => $validated['payment_term'],
                'status' => 'draft',
                'sub_total' => $subTotal,
                'discount' => 0,
                'tax' => 0,
                'shipping_cost' => 0,
                'total_amount' => $subTotal,
                'paid_amount' => 0,
                'due_amount' => $subTotal,
                'notes' => $validated['notes'] ?? null,
                'created_by' => null,
            ]);

            foreach ($itemsData as $itemData) {
                $sale->items()->create($itemData);
            }

            $customer->incrementOrderCount();

            return $sale;
        });

        return $this->success(
            new SaleResource($sale->load('items.product')),
            'Order placed! It is pending confirmation from ' . ($channel['agent_id'] ? 'your sales agent' : 'the admin') . '.',
            201
        );
    }

    /**
     * Only while still pending - once an agent/admin confirms it, stock and
     * ledger entries exist and cancelling becomes a real reversal, not a
     * customer-facing action (contact the agent/admin instead).
     */
    public function cancel($id)
    {
        $sale = Sale::where('customer_id', $this->customer()->id)->find($id);

        if (!$sale) {
            return $this->error('Order not found.', 404);
        }

        if ($sale->status !== 'draft') {
            return $this->error('This order has already been processed and can no longer be cancelled here - contact your sales agent or the admin.', 422);
        }

        $sale->update(['status' => 'cancelled']);

        return $this->success(new SaleResource($sale), 'Order cancelled.');
    }
}
