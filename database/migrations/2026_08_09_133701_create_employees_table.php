<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // Nullable - only set for employees who also have a software
            // login (auto-linked the moment that User row is created).
            // Employees added directly through this module (operational/
            // supply-chain staff who never log in) leave this null.
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->onDelete('set null');

            $table->string('employee_code')->unique();
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');

            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('cnic')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();

            $table->string('designation')->nullable();
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'intern'])->default('full_time');
            $table->date('date_of_joining')->nullable();
            $table->date('date_of_leaving')->nullable();
            $table->enum('employment_status', ['active', 'on_leave', 'suspended', 'terminated', 'resigned'])->default('active');
            $table->foreignId('reporting_manager_id')->nullable()->constrained('employees')->onDelete('set null');

            // How this record came to exist - auto-linked to a software
            // login, or manually added by admin/HR staff.
            $table->enum('source', ['auto_software_user', 'manually_added'])->default('manually_added');

            // Same onboarding shape already used for Sale Agents
            // (User::is_active/approved_at/approved_by/admin_note) -
            // reused here for consistency rather than a new state machine.
            $table->boolean('is_active')->default(true);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('admin_note')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['employee_code', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
