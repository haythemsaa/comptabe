<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vat_declarations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();

            $table->enum('period_type', ['monthly', 'quarterly']);
            $table->integer('period_year');
            $table->integer('period_number'); // 1-12 for monthly, 1-4 for quarterly

            $table->enum('status', ['draft', 'validated', 'submitted', 'accepted', 'rejected'])->default('draft');

            // Grid values (Belgian VAT declaration grids)
            $table->json('grid_values'); // {00: x, 01: x, 02: x, ... 71: x}

            // Totals
            $table->decimal('total_operations', 15, 2)->default(0);
            $table->decimal('total_vat_due', 15, 2)->default(0);
            $table->decimal('total_vat_deductible', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);

            // Submission
            $table->timestamp('validated_at')->nullable();
            $table->foreignUuid('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->string('submission_reference', 100)->nullable();
            $table->json('intervat_response')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['company_id', 'period_year', 'period_type', 'period_number'], 'vat_declaration_unique');
            $table->index(['company_id', 'status']);
        });

        // Client listing (annual)
        Schema::create('vat_client_listings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();

            $table->integer('year');
            $table->enum('status', ['draft', 'validated', 'submitted', 'accepted'])->default('draft');

            $table->json('clients'); // [{vat_number, turnover, vat}]
            $table->decimal('total_turnover', 15, 2)->default(0);
            $table->decimal('total_vat', 15, 2)->default(0);
            $table->integer('client_count')->default(0);

            $table->timestamp('submitted_at')->nullable();
            $table->string('submission_reference', 100)->nullable();

            $table->timestamps();

            $table->unique(['company_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vat_client_listings');
        Schema::dropIfExists('vat_declarations');
    }
};
