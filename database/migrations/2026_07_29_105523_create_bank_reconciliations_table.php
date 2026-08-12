<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->date('statement_date');
            $table->decimal('statement_balance', 15, 2);
            $table->decimal('system_balance', 15, 2);
            $table->decimal('difference', 15, 2);
            $table->enum('status', ['pending', 'reconciled', 'adjusted'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reconciled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['account_id', 'statement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliations');
    }
};
