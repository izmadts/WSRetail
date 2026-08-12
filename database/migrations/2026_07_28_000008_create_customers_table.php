<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('cnic')->nullable();
            $table->string('ntn')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->integer('credit_days')->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by_agent_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_agent_customer')->default(false);
            $table->timestamp('activated_at')->nullable();
            $table->integer('order_count')->default(0);
            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['code', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};