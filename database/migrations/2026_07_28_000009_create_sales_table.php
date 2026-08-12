<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('agent_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('sale_date');
            $table->date('due_date')->nullable();
            $table->enum('payment_term', ['cash', 'credit'])->default('cash');
            $table->enum('status', ['draft', 'confirmed', 'partial', 'paid', 'cancelled'])->default('draft');
            
            $table->decimal('sub_total', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->enum('discount_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            
            $table->decimal('commission_amount', 15, 2)->default(0);
            $table->decimal('commission_paid_amount', 15, 2)->default(0);
            $table->decimal('commission_due_amount', 15, 2)->default(0);
            $table->decimal('recovery_percentage', 15, 2)->default(0);
            $table->boolean('is_commission_held')->default(false);
            $table->text('commission_hold_reason')->nullable();
            
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('due_amount', 15, 2)->default(0);
            
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('commission_paid_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['invoice_no', 'customer_id', 'agent_id', 'sale_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};