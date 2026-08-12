<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The AgentCommissionPayment model has existed with no backing table at
 * all - this is that table, redesigned as a payout-transaction ledger
 * (see the model's docblock) rather than a near-duplicate of
 * agent_commission_logs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_commission_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque']);
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['agent_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_commission_payments');
    }
};
