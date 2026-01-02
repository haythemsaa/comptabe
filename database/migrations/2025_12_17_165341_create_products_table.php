<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();

            // Basic info
            $table->string('code', 50)->nullable(); // SKU/Reference
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['product', 'service'])->default('service');

            // Pricing
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->string('unit', 50)->default('unité'); // unit, hour, day, kg, etc.
            $table->decimal('vat_rate', 5, 2)->default(21.00);

            // Category/Organization
            $table->string('category', 100)->nullable();
            $table->integer('sort_order')->default(0);

            // Status
            $table->boolean('is_active')->default(true);

            // Accounting
            $table->string('accounting_code', 20)->nullable(); // Revenue account code

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'category']);
        });

        // Add unique constraint separately to handle nullable code
        Schema::table('products', function (Blueprint $table) {
            $table->unique(['company_id', 'code'], 'products_company_code_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
