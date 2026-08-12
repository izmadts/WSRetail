<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->orderBy('name')->get();
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
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
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
        ]);

        // Defaulting a missing key to true here would make an explicit
        // uncheck of either box unrepresentable - unchecked checkboxes send
        // no key at all, so "true" and "absent" would be indistinguishable
        // and the guard below could never actually trigger. The form's own
        // checked-by-default markup is what gives a fresh create both true.
        $validated['is_retail'] = $request->boolean('is_retail');
        $validated['is_wholesale'] = $request->boolean('is_wholesale');

        if (!$validated['is_retail'] && !$validated['is_wholesale']) {
            return back()->withErrors(['is_retail' => 'Product must be available for at least Retail or Wholesale.'])->withInput();
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

        $validated['wholesale_price'] = $validated['wholesale_price'] ?? $validated['sale_price'];
        $validated['current_stock'] = $validated['current_stock'] ?? 0;
        $validated['min_stock_level'] = $validated['min_stock_level'] ?? 0;
        $validated['max_stock_level'] = $validated['max_stock_level'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active', true);

        $product = Product::create($validated);
        $product->postOpeningStock();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully!');
    }

    public function show(Product $product)
    {
        $product->load('category', 'stockMovements');
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::active()->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', Rule::unique('products')->ignore($product->id)],
            'name' => ['required', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            'category_id' => 'required|exists:categories,id',
            'unit' => 'required|string|max:50',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'min_stock_level' => 'nullable|numeric|min:0',
            'max_stock_level' => 'nullable|numeric|min:0',
            'barcode' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'is_retail' => 'boolean',
            'is_wholesale' => 'boolean',
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

        if (!$validated['is_retail'] && !$validated['is_wholesale']) {
            return back()->withErrors(['is_retail' => 'Product must be available for at least Retail or Wholesale.'])->withInput();
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

        $validated['wholesale_price'] = $validated['wholesale_price'] ?? $validated['sale_price'];
        $validated['min_stock_level'] = $validated['min_stock_level'] ?? 0;
        $validated['max_stock_level'] = $validated['max_stock_level'] ?? 0;
        // is_active was never assigned from the request at all - an
        // unchecked checkbox sends no key, so it was silently missing from
        // $validated and the mass update left the column untouched no
        // matter what the admin selected.
        $validated['is_active'] = $request->boolean('is_active');

        $product->update($validated);

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
        // once the product has any real history.
        $hasHistory = $product->saleItems()->exists()
            || $product->purchaseItems()->exists()
            || $product->stockMovements()->exists();

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
}