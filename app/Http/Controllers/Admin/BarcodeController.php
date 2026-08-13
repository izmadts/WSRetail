<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PosSetting;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function index()
    {
        // A variant product has nothing of its own worth printing a
        // barcode for (see ProductVariant) - listed here as its individual
        // variants instead, each with its own printable SKU/barcode.
        $products = Product::active()->where('has_variants', false)
            ->orderBy('name')->get(['id', 'name', 'code', 'barcode', 'sale_price']);

        $variants = ProductVariant::where('is_active', true)
            ->whereHas('product', fn ($q) => $q->where('is_active', true))
            ->with('product:id,name')
            ->orderBy('label')
            ->get(['id', 'product_id', 'label', 'sku', 'barcode', 'sale_price']);

        return view('admin.products.barcode', compact('products', 'variants'));
    }

    public function print(Request $request)
    {
        $validated = $request->validate([
            'qty' => 'required|array',
            'qty.*' => 'nullable|integer|min:0|max:500',
        ]);

        // Keys are "p{id}" (product) or "v{id}" (variant) - the form
        // submits every row with a qty input (most left at 0), only the
        // ones an admin actually bumped up matter here.
        $requested = collect($validated['qty'])->filter(fn ($qty) => (int) $qty > 0);

        if ($requested->isEmpty()) {
            return back()->with('error', 'Enter a quantity for at least one product.');
        }

        $productIds = $requested->keys()->filter(fn ($k) => str_starts_with($k, 'p'))->map(fn ($k) => (int) substr($k, 1));
        $variantIds = $requested->keys()->filter(fn ($k) => str_starts_with($k, 'v'))->map(fn ($k) => (int) substr($k, 1));

        $products = Product::whereIn('id', $productIds)->get()->keyBy(fn ($p) => 'p' . $p->id);
        $variants = ProductVariant::with('product:id,name')->whereIn('id', $variantIds)->get()->keyBy(fn ($v) => 'v' . $v->id);

        // Normalized to the same shape the print view already expects
        // (name/code/barcode/sale_price), regardless of whether it came
        // from a plain product or a variant - the print view/JS needs no
        // knowledge of the difference.
        $items = $products->map(fn ($p) => (object) [
            'name' => $p->name,
            'code' => $p->code,
            'barcode' => $p->barcode,
            'sale_price' => $p->sale_price,
        ])->union($variants->map(fn ($v) => (object) [
            'name' => $v->product ? "{$v->product->name} ({$v->label})" : $v->label,
            'code' => $v->sku,
            'barcode' => $v->barcode,
            'sale_price' => $v->sale_price,
        ]));

        // Expand into one label per copy requested - simplest way to hand
        // the print view a flat list to lay out.
        $labels = $requested->flatMap(fn ($qty, $key) => $items->has($key)
            ? array_fill(0, (int) $qty, $items->get($key))
            : []
        );

        $posSettings = PosSetting::current();

        return view('admin.products.barcode-print', compact('labels', 'posSettings'));
    }
}
