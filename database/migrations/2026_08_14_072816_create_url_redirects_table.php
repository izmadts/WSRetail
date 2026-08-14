<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('url_redirects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            // Path only (no scheme/host), as it existed on the OLD store -
            // e.g. "/product/red-t-shirt". Normalized to always start with
            // "/" and never end with one, so a lookup never has to guess
            // formatting. Unique: one old URL always resolves to exactly
            // one destination.
            $table->string('old_path')->unique();
            // Path on the NEW WSRetail storefront, e.g. "/products/123" -
            // stored relative so changing the storefront's own domain later
            // doesn't require rewriting every captured row.
            $table->string('new_path');
            $table->string('source')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('url_redirects');
    }
};
