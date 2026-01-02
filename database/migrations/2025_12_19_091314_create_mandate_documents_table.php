<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mandate_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_mandate_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('uploaded_by')->constrained('users');

            // Document info
            $table->string('name');
            $table->string('file_path', 500);
            $table->string('file_type', 100)->nullable();
            $table->unsignedInteger('file_size')->nullable();

            // Classification
            $table->string('category', 50)->nullable();
            // invoice, receipt, bank_statement, contract, annual_accounts, tax_return, other
            $table->integer('fiscal_year')->nullable();
            $table->string('period', 20)->nullable();

            // Status
            $table->string('status', 20)->default('pending');
            // pending, processing, processed, rejected

            // OCR / AI
            $table->longText('ocr_text')->nullable();
            $table->json('ai_extracted_data')->nullable();

            // Visibility
            $table->boolean('visible_to_client')->default(true);

            // Processing
            $table->timestamp('processed_at')->nullable();
            $table->foreignUuid('processed_by')->nullable()->constrained('users')->nullOnDelete();

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index(['client_mandate_id', 'category']);
            $table->index('fiscal_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mandate_documents');
    }
};
