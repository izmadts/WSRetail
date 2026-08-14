<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Set true only on the account Settings > General > Demo Mode
            // provisions (see SettingsController::updateGeneral()) - lets
            // the app tell "this login IS the public demo account" apart
            // from a real admin, even though both have role=admin (a demo
            // needs full admin capability to actually showcase the
            // software). Used to hide/block the Demo Mode block itself so
            // a demo visitor can't disable the demo or change its own
            // credentials and lock out the next visitor.
            $table->boolean('is_demo_account')->default(false)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_demo_account');
        });
    }
};
