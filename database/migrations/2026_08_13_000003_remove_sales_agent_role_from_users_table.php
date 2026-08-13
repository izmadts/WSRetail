<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Sale Agent module removed - no user should carry this role by the
        // time this runs (verified against the real dev DB before writing
        // this migration; the one placeholder row that had it was deleted).
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','manager','accountant','pos_manager') DEFAULT 'pos_manager'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','manager','accountant','sales_agent','pos_manager') DEFAULT 'sales_agent'");
    }
};
