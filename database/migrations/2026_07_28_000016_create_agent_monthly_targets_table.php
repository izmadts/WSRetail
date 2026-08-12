<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_monthly_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->year('year');
            $table->tinyInteger('month');
            $table->decimal('target_amount', 15, 2)->default(0);
            $table->decimal('achieved_amount', 15, 2)->default(0);
            $table->decimal('achievement_percentage', 15, 2)->default(0);
            $table->decimal('bonus_earned', 15, 2)->default(0);
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
            
            $table->unique(['agent_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_monthly_targets');
    }
};