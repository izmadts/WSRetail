<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Defense in depth alongside removing the open self-registration route
 * (routes/auth.php) that exploited this: the users.role column has
 * defaulted to 'admin' since 0001_01_01_000000_create_users_table.php, so
 * ANY future code path that inserts a user row without explicitly setting
 * role - a raw DB::table('users')->insert(), a factory, an import script,
 * a package - would silently create a full admin account. 'sales_agent' is
 * the least-privileged value in the enum and (unlike admin/manager/
 * accountant) is meaningless without a separate approval step elsewhere in
 * the app, so it's the safer thing to fail open to if this ever happens
 * again. Every real user-creation path in this codebase already sets role
 * explicitly (confirmed via grep), so this only changes behavior for
 * code that was already relying on an implicit default - which should not
 * exist, but if it does, this is the reason it's now safer.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'manager', 'accountant', 'sales_agent') DEFAULT 'sales_agent'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'manager', 'accountant', 'sales_agent') DEFAULT 'admin'");
    }
};
