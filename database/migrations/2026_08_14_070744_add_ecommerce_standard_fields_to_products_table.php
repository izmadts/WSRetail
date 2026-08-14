<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fields every standard e-commerce CMS (WooCommerce/Shopify/Magento)
     * exposes on a product that this table had nowhere to put, found while
     * auditing for import fidelity ahead of building more connectors -
     * short_description, brand/vendor, and shipping weight/dimensions.
     * All nullable: existing rows and every other flow are unaffected.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('short_description')->nullable()->after('description');
            $table->string('brand')->nullable()->after('short_description');
            $table->decimal('weight', 10, 3)->nullable()->after('max_stock_level');
            $table->decimal('length', 10, 2)->nullable()->after('weight');
            $table->decimal('width', 10, 2)->nullable()->after('length');
            $table->decimal('height', 10, 2)->nullable()->after('width');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['short_description', 'brand', 'weight', 'length', 'width', 'height']);
        });
    }
};
