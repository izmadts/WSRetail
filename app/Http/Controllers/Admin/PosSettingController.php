<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Location;
use App\Models\PosSetting;
use Illuminate\Http\Request;

class PosSettingController extends Controller
{
    private const PAYMENT_METHODS = ['cash', 'bank_transfer', 'cheque', 'credit_card'];

    public function edit()
    {
        $posSetting = PosSetting::current();
        $locations = Location::active()->orderBy('name')->get();
        $customers = Customer::active()->orderBy('name')->get();

        return view('admin.settings.pos.edit', compact('posSetting', 'locations', 'customers'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'default_location_id' => 'nullable|exists:locations,id',
            'default_customer_id' => 'nullable|exists:customers,id',
            'products_per_page' => 'required|integer|min:6|max:200',
            'invoice_paper_size' => 'required|in:thermal_58,thermal_80,a4',
            'auto_print_receipt' => 'boolean',
            'payment_methods' => 'nullable|array',
            'payment_methods.*' => 'in:' . implode(',', self::PAYMENT_METHODS),
            'barcode_format' => 'required|in:CODE128,EAN13,CODE39',
            'barcode_label_width_mm' => 'required|numeric|min:10|max:200',
            'barcode_label_height_mm' => 'required|numeric|min:10|max:200',
            'barcode_columns_per_row' => 'required|integer|min:1|max:10',
            'barcode_show_name' => 'boolean',
            'barcode_show_price' => 'boolean',
        ]);

        $validated['auto_print_receipt'] = $request->boolean('auto_print_receipt');
        $validated['barcode_show_name'] = $request->boolean('barcode_show_name');
        $validated['barcode_show_price'] = $request->boolean('barcode_show_price');
        // At least 'cash' always enabled - an empty list would leave the
        // POS payment-method selector with nothing to choose from.
        $validated['payment_methods'] = $request->filled('payment_methods') ? $validated['payment_methods'] : ['cash'];

        PosSetting::current()->update($validated);

        return redirect()->route('admin.settings.pos.edit')
            ->with('success', 'POS settings updated successfully!');
    }
}
