<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Singleton table - always exactly one row (id=1), same pattern
        // intent as the key/value `settings` table but with real columns
        // since every field here is POS-specific and always present.
        Schema::create('pos_settings', function (Blueprint $table) {
            $table->id();

            // Defaults - what the POS screen preselects when an admin/
            // manager (not locked to one location via users.location_id)
            // opens it fresh.
            $table->foreignId('default_location_id')->nullable()->constrained('locations')->onDelete('set null');
            $table->foreignId('default_customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->unsignedInteger('products_per_page')->default(24);

            // Receipt / printer
            $table->enum('invoice_paper_size', ['thermal_58', 'thermal_80', 'a4'])->default('a4');
            $table->boolean('auto_print_receipt')->default(false);
            $table->json('payment_methods')->nullable();

            // Barcode labels
            $table->enum('barcode_format', ['CODE128', 'EAN13', 'CODE39'])->default('CODE128');
            $table->decimal('barcode_label_width_mm', 5, 1)->default(40.0);
            $table->decimal('barcode_label_height_mm', 5, 1)->default(20.0);
            $table->unsignedTinyInteger('barcode_columns_per_row')->default(3);
            $table->boolean('barcode_show_name')->default(true);
            $table->boolean('barcode_show_price')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_settings');
    }
};
