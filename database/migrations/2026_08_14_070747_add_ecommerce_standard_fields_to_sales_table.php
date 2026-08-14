<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * More fields every standard e-commerce CMS carries on an order that
     * this table had nowhere to put: the actual payment method/gateway
     * name (distinct from payment_term's cash/credit accounting
     * classification), an applied coupon code, and the order's own
     * billing/shipping address. Addresses are a point-in-time JSON
     * snapshot, not a live reference to the customer row - a customer's
     * saved address can change later without rewriting old orders, and a
     * guest/one-off order's ship-to may never have matched any saved
     * customer address at all. Deliberately NOT adding a currency column -
     * WSRetail's accounting is single-currency throughout, so a per-order
     * currency field would be stored but meaningless everywhere else.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('payment_term');
            $table->string('coupon_code')->nullable()->after('discount_type');
            $table->json('billing_address')->nullable()->after('notes');
            $table->json('shipping_address')->nullable()->after('billing_address');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'coupon_code', 'billing_address', 'shipping_address']);
        });
    }
};
