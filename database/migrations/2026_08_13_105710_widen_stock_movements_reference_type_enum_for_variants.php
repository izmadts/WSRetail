<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * A variant's opening stock is posted under 'opening_variant' rather than
 * reusing 'opening' - see the comment in
 * ProductController::postVariantOpeningStock() for why (AccountReconciliationService
 * hardcodes 'opening' reference_id as a Product primary key; reusing it for
 * a variant's id could collide with an unrelated product's id). That value
 * wasn't in the enum yet, so every variant product with opening stock
 * failed its StockMovement insert with a MySQL data-truncation error.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE stock_movements MODIFY reference_type ENUM('purchase', 'purchase_return', 'sale', 'sales_return', 'return', 'adjustment', 'opening', 'opening_variant')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE stock_movements MODIFY reference_type ENUM('purchase', 'purchase_return', 'sale', 'sales_return', 'return', 'adjustment', 'opening')");
    }
};
