<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Nullable, default-off: existing sales and any flow that
            // doesn't set up locations at all keep working unchanged.
            $table->foreignId('location_id')->nullable()->after('agent_id')
                ->constrained('locations')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('location_id');
        });
    }
};
