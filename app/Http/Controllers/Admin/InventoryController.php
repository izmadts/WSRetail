<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function dashboard()
    {
        $totalProducts = Product::count();
        $lowStockCount = Product::lowStock()->count();
        $outOfStockCount = Product::where('current_stock', '<=', 0)->count();
        $totalStockValue = Product::sum(DB::raw('current_stock * purchase_price'));
        $recentMovements = StockMovement::with('product')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // ✅ Get all products with stock and worth
        $products = Product::with('category')
            ->orderBy('name')
            ->get();

        // ✅ Calculate worth for each product
        foreach ($products as $product) {
            $product->worth = $product->current_stock * $product->purchase_price;
        }

        return view('admin.inventory.dashboard', compact(
            'totalProducts',
            'lowStockCount',
            'outOfStockCount',
            'totalStockValue',
            'recentMovements',
            'products'
        ));
    }

    public function history(Request $request)
    {
        $query = StockMovement::with('product');
        
        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }
        
        if ($request->type) {
            $query->where('type', $request->type);
        }
        
        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        $movements = $query->orderBy('created_at', 'desc')->paginate(50);
        $products = Product::active()->orderBy('name')->get();
        
        return view('admin.inventory.history', compact('movements', 'products'));
    }
}