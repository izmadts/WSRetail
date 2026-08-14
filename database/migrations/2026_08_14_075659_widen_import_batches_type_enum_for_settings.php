<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Adds 'settings' to import_batches.type - store info/payment gateways/
     * shipping imported from the old CMS, same stage->review->commit flow
     * as products/customers/orders (see IntegrationImportService::
     * stageSettings()). Raw SQL: Laravel's schema builder can't modify an
     * existing enum's value list without doctrine/dbal.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE import_batches MODIFY COLUMN type ENUM('products', 'customers', 'orders', 'settings') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE import_batches MODIFY COLUMN type ENUM('products', 'customers', 'orders') NOT NULL");
    }
};
