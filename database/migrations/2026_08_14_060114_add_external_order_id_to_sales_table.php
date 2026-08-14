<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lets an imported e-commerce order be found again on a re-import
     * (dedup key) and lets the "E-commerce Store Orders" list under Sales
     * show the platform's own order number instead of just WSRetail's
     * invoice_no. Nullable/unused for every other sale source.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('external_order_id')->nullable()->after('source');
            $table->unique(['source', 'external_order_id']);
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique(['source', 'external_order_id']);
            $table->dropColumn('external_order_id');
        });
    }
};
