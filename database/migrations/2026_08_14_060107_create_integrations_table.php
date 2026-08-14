<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per connected external platform (woocommerce, shopify,
     * magento, whatsapp, sms, ...). credentials is encrypted JSON - shape
     * differs per platform (e.g. WooCommerce: site_url/consumer_key/
     * consumer_secret), so it's not normalized into separate columns.
     */
    public function up(): void
    {
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->string('platform')->unique();
            $table->string('status')->default('disconnected');
            $table->text('credentials')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_error_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrations');
    }
};
