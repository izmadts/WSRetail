<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_reconciliation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconciliation_id')->constrained('bank_reconciliations')->onDelete('cascade');
            $table->enum('type', ['deposit', 'withdrawal', 'transfer', 'fee', 'interest', 'other']);
            $table->date('transaction_date');
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['cleared', 'uncleared', 'adjusted'])->default('uncleared');
            $table->string('reference_no')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliation_items');
    }
};
