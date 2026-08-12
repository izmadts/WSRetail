<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Backs the Ctrl/Cmd+K quick-search palette. Each model group is only
 * searched if the current user has 'view' permission on that module - same
 * rule as the sidebar/Quick Add filtering, so this never surfaces a result
 * the user couldn't actually open.
 */
class QuickSearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['groups' => []]);
        }

        $user = Auth::user();
        $groups = [];

        if ($user->hasPermission('customers', 'view')) {
            $this->addGroup($groups, 'Customers', 'fa-users', 'text-emerald-600',
                Customer::where(fn ($query) => $query->where('name', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%"))
                    ->limit(5)->get(),
                fn ($c) => [
                    'title' => $c->name,
                    'subtitle' => trim(($c->code ?? '') . ' ' . ($c->phone ?? '')),
                    'url' => route('admin.customers.show', $c->id),
                ]);
        }

        if ($user->hasPermission('suppliers', 'view')) {
            $this->addGroup($groups, 'Suppliers', 'fa-truck', 'text-purple-700',
                Supplier::where(fn ($query) => $query->where('name', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%"))
                    ->limit(5)->get(),
                fn ($s) => [
                    'title' => $s->name,
                    'subtitle' => trim(($s->code ?? '') . ' ' . ($s->phone ?? '')),
                    'url' => route('admin.suppliers.show', $s->id),
                ]);
        }

        if ($user->hasPermission('products', 'view')) {
            $this->addGroup($groups, 'Products', 'fa-box', 'text-yellow-600',
                Product::where(fn ($query) => $query->where('name', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%")
                        ->orWhere('barcode', 'like', "%{$q}%"))
                    ->limit(5)->get(),
                fn ($p) => [
                    'title' => $p->name,
                    'subtitle' => trim(($p->code ?? '') . ' - Stock: ' . rtrim(rtrim(number_format((float) $p->current_stock, 2), '0'), '.')),
                    'url' => route('admin.products.show', $p->id),
                ]);
        }

        if ($user->hasPermission('sales', 'view')) {
            $this->addGroup($groups, 'Sales', 'fa-shopping-bag', 'text-emerald-600',
                Sale::with('customer')->where('invoice_no', 'like', "%{$q}%")->limit(5)->get(),
                fn ($s) => [
                    'title' => $s->invoice_no,
                    'subtitle' => trim(($s->customer->name ?? '') . ' \xC2\xB7 Rs. ' . number_format((float) $s->total_amount, 2)),
                    'url' => route('admin.sales.show', $s->id),
                ]);
        }

        if ($user->hasPermission('purchases', 'view')) {
            $this->addGroup($groups, 'Purchases', 'fa-shopping-cart', 'text-purple-700',
                Purchase::with('supplier')->where('invoice_no', 'like', "%{$q}%")->limit(5)->get(),
                fn ($p) => [
                    'title' => $p->invoice_no,
                    'subtitle' => trim(($p->supplier->name ?? '') . ' \xC2\xB7 Rs. ' . number_format((float) $p->total_amount, 2)),
                    'url' => route('admin.purchases.show', $p->id),
                ]);
        }

        return response()->json(['groups' => $groups]);
    }

    private function addGroup(array &$groups, string $label, string $icon, string $color, $records, callable $map): void
    {
        if ($records->isEmpty()) {
            return;
        }

        $groups[] = [
            'label' => $label,
            'icon' => $icon,
            'color' => $color,
            'items' => $records->map($map)->values(),
        ];
    }
}
