<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'variants'])->orderBy('name')->get();
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::active()->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|unique:products',
            'name' => 'required|string|max:255|unique:products',
            'category_id' => 'required|exists:categories,id',
            'unit' => 'required|string|max:50',
            'purchase_price' => 'required_if:has_variants,0|nullable|numeric|min:0',
            'sale_price' => 'required_if:has_variants,0|nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'current_stock' => 'nullable|numeric|min:0',
            'min_stock_level' => 'nullable|numeric|min:0',
            'max_stock_level' => 'nullable|numeric|min:0',
            'barcode' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'is_retail' => 'boolean',
            'is_wholesale' => 'boolean',
            'has_variants' => 'boolean',
            'variants_json' => 'required_if:has_variants,1|nullable|string',
        ]);

        // Defaulting a missing key to true here would make an explicit
        // uncheck of either box unrepresentable - unchecked checkboxes send
        // no key at all, so "true" and "absent" would be indistinguishable
        // and the guard below could never actually trigger. The form's own
        // checked-by-default markup is what gives a fresh create both true.
        $validated['is_retail'] = $request->boolean('is_retail');
        $validated['is_wholesale'] = $request->boolean('is_wholesale');
        $validated['has_variants'] = $request->boolean('has_variants');

        if (!$validated['is_retail'] && !$validated['is_wholesale']) {
            return back()->withErrors(['is_retail' => 'Product must be available for at least Retail or Wholesale.'])->withInput();
        }

        $variants = null;
        if ($validated['has_variants']) {
            $variants = $this->parseAndValidateVariants($request->input('variants_json'));
            if ($variants instanceof \Illuminate\Http\RedirectResponse) {
                return $variants;
            }
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->extension();
            $image->move(public_path('uploads/products'), $imageName);
            $validated['image'] = 'uploads/products/' . $imageName;
        }

        if (empty($validated['code'])) {
            $validated['code'] = 'PRD-' . strtoupper(Str::random(8));
        }

        // A variant product's own price/stock columns are unused (each
        // variant carries its own - see ProductVariant) - zeroed out here
        // rather than left as whatever the disabled form fields happened to
        // hold, so the parent row can never be mistaken for real data.
        if ($validated['has_variants']) {
            $validated['purchase_price'] = 0;
            $validated['sale_price'] = 0;
            $validated['wholesale_price'] = 0;
            $validated['current_stock'] = 0;
        } else {
            $validated['wholesale_price'] = $validated['wholesale_price'] ?? $validated['sale_price'];
            $validated['current_stock'] = $validated['current_stock'] ?? 0;
        }
        $validated['min_stock_level'] = $validated['min_stock_level'] ?? 0;
        $validated['max_stock_level'] = $validated['max_stock_level'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active', true);

        unset($validated['variants_json']);

        $product = DB::transaction(function () use ($validated, $variants) {
            $product = Product::create($validated);

            if ($variants) {
                $this->saveVariants($product, $variants);
            }

            return $product;
        });

        $product->postOpeningStock();
        foreach ($product->variants as $variant) {
            $this->postVariantOpeningStock($product, $variant);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully!');
    }

    public function show(Product $product)
    {
        $product->load('category', 'stockMovements', 'variants.attributeValues.attribute');
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::active()->get();
        $product->load('variants.attributeValues.attribute');
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', Rule::unique('products')->ignore($product->id)],
            'name' => ['required', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            'category_id' => 'required|exists:categories,id',
            'unit' => 'required|string|max:50',
            'purchase_price' => 'required_if:has_variants,0|nullable|numeric|min:0',
            'sale_price' => 'required_if:has_variants,0|nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'min_stock_level' => 'nullable|numeric|min:0',
            'max_stock_level' => 'nullable|numeric|min:0',
            'barcode' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'is_retail' => 'boolean',
            'is_wholesale' => 'boolean',
            'has_variants' => 'boolean',
            'existing_variants_json' => 'nullable|string',
            'new_variants_json' => 'nullable|string',
        ]);

        // current_stock is intentionally not validated/accepted here - the
        // edit form marks it read-only, but that's only enforced client-side
        // unless it's also dropped from the mass-assigned data. Stock may
        // only change via sales, purchases, returns, and stock adjustments,
        // each of which leaves a StockMovement + ledger trail; a direct
        // product edit must not be able to silently desync it.
        unset($validated['current_stock']);

        $validated['is_retail'] = $request->boolean('is_retail');
        $validated['is_wholesale'] = $request->boolean('is_wholesale');
        // A product that already has variants can't have that turned off
        // here - it's a one-way switch made at creation, since flipping it
        // off would orphan every existing variant's sale/purchase history.
        $validated['has_variants'] = $product->has_variants || $request->boolean('has_variants');

        if (!$validated['is_retail'] && !$validated['is_wholesale']) {
            return back()->withErrors(['is_retail' => 'Product must be available for at least Retail or Wholesale.'])->withInput();
        }

        $newVariants = null;
        if ($validated['has_variants'] && $request->filled('new_variants_json')) {
            $newVariants = $this->parseAndValidateVariants($request->input('new_variants_json'));
            if ($newVariants instanceof \Illuminate\Http\RedirectResponse) {
                return $newVariants;
            }
        }

        if ($request->hasFile('image')) {
            if ($product->image && file_exists(public_path($product->image))) {
                unlink(public_path($product->image));
            }

            $image = $request->file('image');
            $imageName = time() . '.' . $image->extension();
            $image->move(public_path('uploads/products'), $imageName);
            $validated['image'] = 'uploads/products/' . $imageName;
        }

        // A variant product doesn't send sale_price at all (its own price
        // columns are unused, disabled on the form) - the fallback below
        // only makes sense when it was actually submitted.
        if (!$validated['has_variants']) {
            $validated['wholesale_price'] = $validated['wholesale_price'] ?? $validated['sale_price'];
        }
        $validated['min_stock_level'] = $validated['min_stock_level'] ?? 0;
        $validated['max_stock_level'] = $validated['max_stock_level'] ?? 0;
        // is_active was never assigned from the request at all - an
        // unchecked checkbox sends no key, so it was silently missing from
        // $validated and the mass update left the column untouched no
        // matter what the admin selected.
        $validated['is_active'] = $request->boolean('is_active');

        if ($validated['has_variants']) {
            $validated['purchase_price'] = 0;
            $validated['sale_price'] = 0;
            $validated['wholesale_price'] = 0;
        }

        $existingVariantEdits = $request->filled('existing_variants_json')
            ? json_decode($request->input('existing_variants_json'), true)
            : [];

        unset($validated['existing_variants_json'], $validated['new_variants_json']);

        $createdVariants = collect();

        DB::transaction(function () use ($validated, $product, $existingVariantEdits, $newVariants, &$createdVariants) {
            $product->update($validated);

            foreach ($existingVariantEdits ?? [] as $edit) {
                $variant = $product->variants()->find($edit['id'] ?? null);
                if (!$variant) {
                    continue;
                }
                $variant->update([
                    'sku' => $edit['sku'] ?? $variant->sku,
                    'barcode' => $edit['barcode'] ?? null,
                    'purchase_price' => $edit['purchase_price'] ?? 0,
                    'sale_price' => $edit['sale_price'] ?? 0,
                    'wholesale_price' => $edit['wholesale_price'] ?? 0,
                    'min_stock_level' => $edit['min_stock_level'] ?? 0,
                    'max_stock_level' => $edit['max_stock_level'] ?? 0,
                    'is_active' => !empty($edit['is_active']),
                ]);
            }

            if ($newVariants) {
                $createdVariants = $this->saveVariants($product, $newVariants);
            }
        });

        // Only the variants just created - NOT product->variants as a
        // whole, which would re-post (and double the ledger for) every
        // variant from a previous edit too.
        foreach ($createdVariants as $variant) {
            $this->postVariantOpeningStock($product, $variant);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        // A hard delete here would cascade and wipe out sale/purchase line
        // items and stock history for this product (see the FKs on
        // sale_items/purchase_items/stock_movements/stock_adjustments) while
        // leaving the journal entries already posted for those transactions
        // behind, unable to be traced back to anything. Block it outright
        // once the product has any real history - including through any of
        // its variants, since product_variants cascade-deletes with the
        // product while sale/purchase/stock rows only null out their
        // product_variant_id, which would silently strip which variant was
        // actually sold from otherwise-intact history.
        $hasHistory = $product->saleItems()->exists()
            || $product->purchaseItems()->exists()
            || $product->stockMovements()->exists()
            || \App\Models\SaleItem::whereIn('product_variant_id', $product->variants()->pluck('id'))->exists()
            || \App\Models\PurchaseItem::whereIn('product_variant_id', $product->variants()->pluck('id'))->exists()
            || \App\Models\StockMovement::whereIn('product_variant_id', $product->variants()->pluck('id'))->exists();

        if ($hasHistory) {
            return back()->with('error', 'Cannot delete this product - it has sales, purchases, or stock history. Deactivate it instead.');
        }

        if ($product->image && file_exists(public_path($product->image))) {
            unlink(public_path($product->image));
        }

        $product->delete();
        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully!');
    }

    public function toggleStatus(Product $product)
    {
        $product->is_active = !$product->is_active;
        $product->save();
        
        return back()->with('success', 'Product status updated!');
    }

    public function lowStock()
    {
        $products = Product::with('category')->lowStock()->get();
        return view('admin.products.low-stock', compact('products'));
    }

    /**
     * Decodes + validates the variant-builder's payload. Returns the
     * decoded array on success, or a redirect-back-with-errors response the
     * caller must return immediately if validation failed - kept a plain
     * method rather than a Form Request since the shape being validated is
     * inside a JSON string field, not a normal nested form array.
     */
    private function parseAndValidateVariants(?string $json)
    {
        $variants = json_decode($json ?? '', true);

        if (!is_array($variants) || count($variants) === 0) {
            return back()->withErrors(['variants_json' => 'Generate at least one variant.'])->withInput();
        }

        $seenSkus = [];
        foreach ($variants as $i => $row) {
            $sku = trim($row['sku'] ?? '');
            if ($sku === '') {
                return back()->withErrors(['variants_json' => "Variant #" . ($i + 1) . " is missing a SKU."])->withInput();
            }
            if (isset($seenSkus[$sku]) || ProductVariant::where('sku', $sku)->exists()) {
                return back()->withErrors(['variants_json' => "SKU \"{$sku}\" is already used by another variant."])->withInput();
            }
            $seenSkus[$sku] = true;

            if (empty($row['attribute_value_ids']) || !is_array($row['attribute_value_ids'])) {
                return back()->withErrors(['variants_json' => "Variant #" . ($i + 1) . " has no attribute values."])->withInput();
            }
        }

        return $variants;
    }

    /**
     * @return \Illuminate\Support\Collection<int, ProductVariant> the
     *     variants just created - callers use this to post opening stock
     *     for ONLY these, never by re-scanning $product->variants (which
     *     would include already-posted ones and double their ledger entry
     *     on every subsequent edit that adds more variants).
     */
    private function saveVariants(Product $product, array $variants): \Illuminate\Support\Collection
    {
        return collect($variants)->map(function ($row) use ($product) {
            $variant = $product->variants()->create([
                'sku' => trim($row['sku']),
                'barcode' => $row['barcode'] ?? null,
                'label' => $row['label'] ?? '',
                'purchase_price' => $row['purchase_price'] ?? 0,
                'sale_price' => $row['sale_price'] ?? 0,
                'wholesale_price' => $row['wholesale_price'] ?? ($row['sale_price'] ?? 0),
                'current_stock' => $row['stock'] ?? 0,
                'min_stock_level' => $row['min_stock_level'] ?? 0,
                'max_stock_level' => $row['max_stock_level'] ?? 0,
                'is_active' => true,
            ]);

            $variant->attributeValues()->sync($row['attribute_value_ids']);

            return $variant;
        });
    }

    /**
     * Mirrors Product::postOpeningStock() for a single variant - a variant
     * created with a non-zero starting stock gets a real 'opening'
     * StockMovement + ledger entry instead of the number just sitting in
     * current_stock with nothing behind it. Idempotent the same way.
     */
    private function postVariantOpeningStock(Product $product, ProductVariant $variant): void
    {
        if ((float) $variant->current_stock <= 0) {
            return;
        }

        // Deliberately a DIFFERENT reference_type from Product's own
        // 'opening' (not just a different reference_id) - AccountReconciliationService
        // hardcodes 'opening' reference_id as a Product primary key
        // (App\Services\AccountReconciliationService::TWO_ROW_TYPES mapping
        // et al.); reusing it with a variant's id here would let a variant
        // id collide with an unrelated product's id and corrupt that
        // reconciliation tool's scan.
        if (\App\Models\StockMovement::where('reference_type', 'opening_variant')
            ->where('product_variant_id', $variant->id)->exists()) {
            return;
        }

        \App\Models\StockMovement::create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'type' => 'in',
            'reference_type' => 'opening_variant',
            'reference_id' => $variant->id,
            'quantity' => $variant->current_stock,
            'unit_price' => $variant->purchase_price,
            'total_price' => round((float) $variant->current_stock * (float) $variant->purchase_price, 2),
            'stock_before' => 0,
            'stock_after' => $variant->current_stock,
            'notes' => "Opening stock for {$product->name} ({$variant->label})",
        ]);

        $amount = round((float) $variant->current_stock * (float) $variant->purchase_price, 2);
        if ($amount <= 0) {
            return;
        }

        $inventoryAccount = \App\Models\Account::where('code', '1030')->first();
        $openingEquityAccount = \App\Models\Account::where('code', '3020')->first();

        if (!$inventoryAccount || !$openingEquityAccount) {
            return;
        }

        $product->postDoubleEntry([
            [
                'account_id' => $inventoryAccount->id,
                'type' => 'debit',
                'amount' => $amount,
                'description' => "Opening stock - {$product->name} ({$variant->label})",
            ],
            [
                'account_id' => $openingEquityAccount->id,
                'type' => 'credit',
                'amount' => $amount,
                'description' => "Opening stock - {$product->name} ({$variant->label})",
            ],
        ], 'opening_variant', $variant->id);
    }
}