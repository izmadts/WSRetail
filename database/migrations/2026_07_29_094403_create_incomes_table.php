<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->string('income_no')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('income_categories')->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->date('income_date');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'credit_card'])->default('cash');
            $table->string('reference_no')->nullable();
            $table->enum('source', ['sale', 'investment', 'loan', 'other'])->default('other');
            $table->string('receipt')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['income_date', 'category_id', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
