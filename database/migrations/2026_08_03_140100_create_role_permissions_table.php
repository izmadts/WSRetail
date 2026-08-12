<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lightweight per-module permission matrix keyed by role (not per-user) -
 * deliberately not a full package like spatie/permission, since the app
 * only has 4 fixed roles (admin/manager/accountant/sales_agent) and needs
 * a view/create/edit/delete toggle per module, not arbitrary abilities.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->string('module');
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();

            $table->unique(['role', 'module']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
