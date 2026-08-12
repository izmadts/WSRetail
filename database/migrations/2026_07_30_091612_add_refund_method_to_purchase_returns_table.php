<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_returns', 'refund_method')) {
                $table->enum('refund_method', ['cash', 'credit', 'cheque', 'bank_transfer'])
                    ->default('credit')
                    ->after('total_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_returns', function (Blueprint $table) {
            $table->dropColumn('refund_method');
        });
    }
};
