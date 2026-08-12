<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function index()
    {
        $adjustments = StockAdjustment::with('product', 'createdBy')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.inventory.adjustments.index', compact('adjustments'));
    }

    public function create()
    {
        $products = Product::active()->orderBy('name')->get();
        return view('admin.inventory.adjustments.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated) {
            // Locked for the duration of the transaction so two adjustments
            // submitted for the same product at nearly the same moment
            // can't both read the same starting stock and both pass the
            // sufficiency check below.
            $product = Product::where('id', $validated['product_id'])->lockForUpdate()->firstOrFail();

            // =============================================
            // FIX: Use proper decimal handling with bcmath
            // =============================================
            
            // Get current stock with proper decimal
            $oldStock = (float) $product->current_stock;
            $quantity = (float) $validated['quantity'];
            
            // Use bcmath for precise calculations (if available)
            if (function_exists('bcadd')) {
                // Precise decimal operations
                if ($validated['type'] == 'in') {
                    $newStock = (float) bcadd((string) $oldStock, (string) $quantity, 2);
                } else {
                    if ($oldStock < $quantity) {
                        throw new \Exception('Insufficient stock! Available: ' . number_format($oldStock, 2));
                    }
                    $newStock = (float) bcsub((string) $oldStock, (string) $quantity, 2);
                }
            } else {
                // Fallback: Use round to fix floating point
                if ($validated['type'] == 'in') {
                    $newStock = round($oldStock + $quantity, 2);
                } else {
                    if ($oldStock < $quantity) {
                        throw new \Exception('Insufficient stock! Available: ' . number_format($oldStock, 2));
                    }
                    $newStock = round($oldStock - $quantity, 2);
                }
            }

            // Update product stock
            $product->current_stock = $newStock;
            $product->save();

            // Create adjustment record
            $adjustment = StockAdjustment::create([
                'product_id' => $product->id,
                'type' => $validated['type'],
                'quantity' => $quantity,
                'stock_before' => $oldStock,
                'stock_after' => $newStock,
                'reason' => $validated['reason'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // reference_id ties this movement back to the adjustment that
            // caused it, so destroy() can find and reverse the right one.
            // unit_price/total_price use the product's current cost, so
            // there's a real value on record (needed for the ledger entry
            // posted below, and for the movement history to mean anything).
            $unitPrice = (float) $product->purchase_price;
            StockMovement::create([
                'product_id' => $product->id,
                'type' => $validated['type'],
                'reference_type' => 'adjustment',
                'reference_id' => $adjustment->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => round($quantity * $unitPrice, 2),
                'stock_before' => $oldStock,
                'stock_after' => $newStock,
                'notes' => $validated['reason'] ?? 'Stock adjustment'
            ]);

            $adjustment->postAccounting();
            });
        } catch (\Exception $e) {
            // Catches the insufficient-stock guard above and any
            // chart-of-accounts issue from postAccounting() - without this
            // they surfaced as a raw 500 error page.
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('admin.inventory.adjustments.index')
            ->with('success', 'Stock adjusted successfully!');
    }

    public function destroy(StockAdjustment $adjustment)
    {
        try {
            DB::transaction(function () use ($adjustment) {
            $product = Product::where('id', $adjustment->product_id)->lockForUpdate()->firstOrFail();
            $oldStock = (float) $product->current_stock;
            $quantity = (float) $adjustment->quantity;

            // Reverse the effect: an "in" adjustment added stock, so
            // deleting it removes that stock again (and vice versa for
            // "out"). Deleting the record without this left stock
            // permanently drifted from what was actually adjusted.
            if ($adjustment->type == 'in') {
                if ($oldStock < $quantity) {
                    throw new \Exception("Cannot delete: reversing this adjustment would take {$product->name}'s stock negative (some of it may have been sold or moved since).");
                }
                $newStock = function_exists('bcsub') ? (float) bcsub((string) $oldStock, (string) $quantity, 2) : round($oldStock - $quantity, 2);
            } else {
                $newStock = function_exists('bcadd') ? (float) bcadd((string) $oldStock, (string) $quantity, 2) : round($oldStock + $quantity, 2);
            }

            $product->current_stock = $newStock;
            $product->save();

            StockMovement::where('reference_type', 'adjustment')
                ->where('reference_id', $adjustment->id)
                ->delete();

            $adjustment->reverseAccounting();
            $adjustment->delete();
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Adjustment deleted and stock reversed successfully!');
    }
}