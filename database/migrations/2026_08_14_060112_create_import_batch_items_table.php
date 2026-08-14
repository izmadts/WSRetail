<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One staged row per external record (WooCommerce product/customer/
     * order) within a batch. mapped_payload is what actually gets written
     * to Product/Customer/Sale on commit - admin can uncheck "included" per
     * row on the review screen, but field-level editing is a later
     * follow-up, not v1.
     */
    public function up(): void
    {
        Schema::create('import_batch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_batch_id')->constrained()->cascadeOnDelete();
            $table->string('external_id');
            $table->json('raw_payload');
            $table->json('mapped_payload');
            // What this row will do on commit, decided at staging time by
            // matching external_id/SKU/phone against existing records.
            $table->enum('action', ['create', 'update', 'skip']);
            $table->string('matched_model')->nullable();
            $table->unsignedBigInteger('matched_id')->nullable();
            $table->boolean('included')->default(true);
            $table->enum('status', ['pending', 'committed', 'failed'])->default('pending');
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['import_batch_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_batch_items');
    }
};
