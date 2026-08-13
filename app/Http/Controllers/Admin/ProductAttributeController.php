<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Http\Request;

/**
 * Backs the variant builder on the product create/edit form. index()/data()
 * feed the Alpine picker; store()/storeValue() are the "quick add" endpoints
 * it calls so a merchant typing a brand-new attribute/value (e.g. a
 * "Storage" attribute with "128GB") never has to leave the product form to
 * define it first.
 */
class ProductAttributeController extends Controller
{
    public function index()
    {
        $attributes = ProductAttribute::with('values')->orderBy('name')->get();

        return view('admin.product-attributes.index', compact('attributes'));
    }

    public function data()
    {
        return response()->json(
            ProductAttribute::with('values:id,product_attribute_id,value')
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:product_attributes,name',
        ]);

        $attribute = ProductAttribute::create($validated);

        if ($request->wantsJson()) {
            return response()->json($attribute);
        }

        return back()->with('success', 'Attribute added.');
    }

    public function storeValue(Request $request, ProductAttribute $productAttribute)
    {
        $validated = $request->validate([
            'value' => 'required|string|max:100|unique:product_attribute_values,value,NULL,id,product_attribute_id,' . $productAttribute->id,
        ]);

        $value = $productAttribute->values()->create($validated);

        if ($request->wantsJson()) {
            return response()->json($value);
        }

        return back()->with('success', 'Value added.');
    }
}
