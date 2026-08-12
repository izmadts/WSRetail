<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Which customer channel(s) a sales agent is allowed to work:
     * 'wholesale', 'retail', or 'both'. Only meaningful for role=sales_agent.
     * A plain string (app-validated), not a DB enum, so the allowed values
     * can change later without an ALTER TABLE migration. Null (the default
     * for every existing agent) is treated as unrestricted - equivalent to
     * 'both' - everywhere this is checked, so nothing breaks for agents
     * created before this field existed.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('channel')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('channel');
        });
    }
};
