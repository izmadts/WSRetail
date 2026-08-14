<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // The product's slug AS IT EXISTED on the source platform (e.g.
            // WooCommerce) at import time - kept separate from WSRetail's
            // own auto-generated `slug` column (see Product::boot(), which
            // always overwrites `slug` from the current name on save).
            // Used together with url_redirects to send old, already-indexed
            // store URLs to their new WSRetail product page instead of a
            // 404 - see IntegrationImportService::recordRedirect().
            $table->string('legacy_slug')->nullable()->after('slug');
            $table->index('legacy_slug');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['legacy_slug']);
            $table->dropColumn('legacy_slug');
        });
    }
};
