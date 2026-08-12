<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tags where a sale originated: the web/admin app, the agent mobile
     * app, or a customer placing their own order through the customer API.
     * Lets the pending-order queue (customer_app + status=draft) be found
     * without guessing, and gives reports a real channel breakdown.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('source')->default('web')->after('agent_id');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
