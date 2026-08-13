<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // When set, locks this user's POS screen to exactly this
            // location - no location switcher shown, and the server
            // re-enforces it on submit regardless of what the form posts.
            // Nullable/optional: admin/manager users stay unrestricted.
            $table->foreignId('location_id')->nullable()->after('role')
                ->constrained('locations')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('location_id');
        });
    }
};
