<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sales - drop the composite index that includes agent_id first
        // (MySQL won't drop a column that's part of an index otherwise),
        // then the FK+column, then every commission-specific column.
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['invoice_no', 'customer_id', 'agent_id', 'sale_date']);
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('agent_id');
            $table->dropColumn([
                'commission_amount', 'commission_paid_amount', 'commission_due_amount',
                'recovery_percentage', 'is_commission_held', 'commission_hold_reason',
            ]);
            $table->index(['invoice_no', 'customer_id', 'sale_date']);
        });

        // Customers - agent attribution.
        Schema::table('customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by_agent_id');
            $table->dropColumn('is_agent_customer');
        });

        // Users - agent-only commission/payout/channel fields. Kept:
        // basic_salary/fuel_allowance (shared with the generic Payroll
        // module) and approved_at/approved_by/admin_note (shared "pending
        // approval" staff-account concept, still used generically).
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'channel', 'commission_rate_cash', 'commission_rate_credit',
                'commission_slabs', 'sales_target',
                'payout_account_type', 'payout_account_title',
                'payout_account_number', 'payout_account_provider',
            ]);
        });

        // Agent commission tables - nothing else references these.
        Schema::dropIfExists('agent_commission_payments');
        Schema::dropIfExists('agent_monthly_targets');
        Schema::dropIfExists('agent_commission_logs');
    }

    public function down(): void
    {
        Schema::create('agent_commission_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('sale_id')->nullable()->constrained('sales')->onDelete('set null');
            $table->string('reference_type');
            $table->unsignedBigInteger('reference_id');
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('commission_rate', 8, 2)->default(0);
            $table->string('commission_type')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->date('paid_date')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('agent_monthly_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('target_amount', 15, 2)->default(0);
            $table->decimal('achieved_amount', 15, 2)->default(0);
            $table->decimal('achievement_percentage', 8, 2)->default(0);
            $table->decimal('bonus_earned', 15, 2)->default(0);
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
        });

        Schema::create('agent_commission_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('payment_date');
            $table->string('payment_method')->nullable();
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('channel')->nullable()->after('role');
            $table->decimal('commission_rate_cash', 15, 2)->default(0);
            $table->decimal('commission_rate_credit', 15, 2)->default(1);
            $table->json('commission_slabs')->nullable();
            $table->json('sales_target')->nullable();
            $table->string('payout_account_type')->nullable();
            $table->string('payout_account_title')->nullable();
            $table->string('payout_account_number')->nullable();
            $table->string('payout_account_provider')->nullable();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('created_by_agent_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_agent_customer')->default(false);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['invoice_no', 'customer_id', 'sale_date']);
            $table->foreignId('agent_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('commission_amount', 15, 2)->default(0);
            $table->decimal('commission_paid_amount', 15, 2)->default(0);
            $table->decimal('commission_due_amount', 15, 2)->default(0);
            $table->decimal('recovery_percentage', 15, 2)->default(0);
            $table->boolean('is_commission_held')->default(false);
            $table->text('commission_hold_reason')->nullable();
            $table->index(['invoice_no', 'customer_id', 'agent_id', 'sale_date']);
        });
    }
};
