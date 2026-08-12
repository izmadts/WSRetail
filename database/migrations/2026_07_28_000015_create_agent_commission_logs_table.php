<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_commission_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('sale_id')->nullable()->constrained()->onDelete('set null');
            $table->string('reference_type'); // 'sale', 'target_bonus', 'new_customer_bonus', 'recovery_bonus'
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->decimal('commission_rate', 15, 2);
            $table->string('commission_type'); // 'cash', 'credit', 'bonus'
            $table->text('description')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->date('paid_date')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['agent_id', 'reference_type', 'reference_id']);
            $table->index(['is_paid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_commission_logs');
    }
};