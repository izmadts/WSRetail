<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every standard e-commerce CMS treats product images as a gallery
     * (multiple), not one field - products.image (single) stays as the
     * primary/thumbnail for existing screens, this table is the rest of
     * the gallery. Not yet populated by the WooCommerce importer (that
     * needs a download-and-store step, a separate follow-up) - this
     * migration only makes sure the data has somewhere to go.
     */
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('external_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
