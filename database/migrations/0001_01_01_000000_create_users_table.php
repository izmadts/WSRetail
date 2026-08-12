<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email', 191)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            
            // Role & Status
            $table->enum('role', ['admin', 'manager', 'accountant', 'sales_agent'])->default('admin');
            $table->boolean('is_active')->default(true);
            
            // Personal Information
            $table->string('employee_id')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('cnic')->nullable()->unique();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('whatsapp_number')->nullable();
            
            // Documents
            $table->string('cnic_front_image')->nullable();
            $table->string('cnic_back_image')->nullable();
            $table->string('personal_photo')->nullable();
            
            // Financial
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('fuel_allowance', 15, 2)->default(0);
            $table->decimal('commission_rate_cash', 15, 2)->default(0);
            $table->decimal('commission_rate_credit', 15, 2)->default(1);
            $table->json('commission_slabs')->nullable();
            $table->json('sales_target')->nullable();
            
            // Payout
            $table->string('payout_account_type')->nullable();
            $table->string('payout_account_title')->nullable();
            $table->string('payout_account_number')->nullable();
            $table->string('payout_account_provider')->nullable();
            
            // Approval
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('admin_note')->nullable();
            $table->timestamp('last_login_at')->nullable();
            
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 191)->primary();
            $table->string('token', 191);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id', 191)->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};