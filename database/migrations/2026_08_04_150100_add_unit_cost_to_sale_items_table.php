<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Snapshot of the product's purchase price at the moment the item was
     * sold. Without this, COGS reversal on a later sales return has to
     * re-read the product's CURRENT purchase price, which drifts from what
     * was actually posted at sale time whenever a cost changes in between.
     */
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 15, 2)->nullable()->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('unit_cost');
        });
    }
};
