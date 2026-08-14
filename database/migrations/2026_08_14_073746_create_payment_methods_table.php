<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            // Shown to the shopper at checkout (e.g. bank account details
            // for a manual Bank Transfer) - free text, not structured.
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Sensible defaults for a v1 store with no online gateway yet - an
        // admin can rename/disable/add to these from Settings > Ecommerce >
        // Payment.
        DB::table('payment_methods')->insert([
            [
                'code' => 'cod',
                'name' => 'Cash on Delivery',
                'description' => 'Pay in cash when your order arrives.',
                'is_enabled' => true,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'bank_transfer',
                'name' => 'Bank Transfer',
                'description' => null,
                'is_enabled' => false,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
