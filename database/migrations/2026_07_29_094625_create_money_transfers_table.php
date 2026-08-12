<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('money_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_no')->unique();
            $table->foreignId('from_account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('to_account_id')->constrained('accounts')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->date('transfer_date');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->text('description')->nullable();
            $table->string('reference_no')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['transfer_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('money_transfers');
    }
};
