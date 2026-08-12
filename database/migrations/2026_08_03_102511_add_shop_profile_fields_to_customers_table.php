<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'shop_name')) {
                $table->string('shop_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('customers', 'gps_location')) {
                $table->string('gps_location')->nullable()->after('address');
            }
            if (!Schema::hasColumn('customers', 'shop_picture')) {
                $table->string('shop_picture')->nullable()->after('gps_location');
            }
            if (!Schema::hasColumn('customers', 'category')) {
                $table->string('category')->nullable()->after('shop_picture');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'shop_name',
                'gps_location',
                'shop_picture',
                'category'
            ]);
        });
    }
};
