<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Which Product price column this group's sales should use.
            $table->enum('price_field', ['sale_price', 'wholesale_price'])->default('sale_price');
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('customer_group_id')->nullable()->after('is_agent_customer')
                ->constrained('customer_groups')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_group_id');
        });
        Schema::dropIfExists('customer_groups');
    }
};
