<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Without this, paying an agent Rs. 100 against a Rs. 5,000 due log flips
 * the WHOLE log to is_paid=true regardless of amount - partial commission
 * payments were impossible to represent correctly.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_commission_logs', function (Blueprint $table) {
            $table->decimal('paid_amount', 15, 2)->default(0)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('agent_commission_logs', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
        });
    }
};
