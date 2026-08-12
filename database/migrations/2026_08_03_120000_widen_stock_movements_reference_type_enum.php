<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The original enum only allowed purchase/sale/return/adjustment/opening,
 * but SalesReturn and PurchaseReturn both actually write 'sales_return' and
 * 'purchase_return' - neither of which fit, so every stock movement either
 * of those models tried to create failed with a MySQL data-truncation
 * error, silently breaking both returns features whenever a transaction
 * reached that insert.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE stock_movements MODIFY reference_type ENUM('purchase', 'purchase_return', 'sale', 'sales_return', 'return', 'adjustment', 'opening')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE stock_movements MODIFY reference_type ENUM('purchase', 'sale', 'return', 'adjustment', 'opening')");
    }
};
