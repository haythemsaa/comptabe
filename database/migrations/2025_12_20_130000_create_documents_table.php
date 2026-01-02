<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Document archive system for paper documents, photos, and scanned files.
     */
    public function up(): void
    {
        // Document folders for organization
        Schema::create('document_folders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('parent_id')->nullable()->constrained('document_folders')->nullOnDelete();
            $table->string('name');
            $table->string('color', 20)->nullable();
            $table->string('icon', 50)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_system')->default(false); // System folders can't be deleted
            $table->timestamps();

            $table->index(['company_id', 'parent_id']);
        });

        // Documents table
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('folder_id')->nullable()->constrained('document_folders')->nullOnDelete();
            $table->foreignUuid('uploaded_by')->nullable()->constrained('users')->nullOnDelete();

            // File information
            $table->string('name'); // Display name
            $table->string('original_filename');
            $table->string('file_path');
            $table->string('disk')->default('local'); // local, s3, etc.
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size'); // in bytes
            $table->string('extension', 10);

            // Document metadata
            $table->enum('type', [
                'invoice',           // Facture scannée
                'receipt',           // Ticket de caisse
                'bank_statement',    // Extrait bancaire
                'contract',          // Contrat
                'tax_document',      // Document fiscal
                'payroll',           // Fiche de paie
                'correspondence',    // Correspondance
                'identity',          // Document d'identité
                'other'              // Autre
            ])->default('other');

            $table->date('document_date')->nullable(); // Date du document
            $table->string('reference', 100)->nullable(); // Numéro de référence
            $table->text('description')->nullable();
            $table->text('notes')->nullable();

            // OCR and search
            $table->longText('ocr_content')->nullable(); // Extracted text from OCR
            $table->boolean('ocr_processed')->default(false);
            $table->timestamp('ocr_processed_at')->nullable();

            // Thumbnail for images/PDFs
            $table->string('thumbnail_path')->nullable();

            // Links to other entities
            $table->foreignUuid('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('partner_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('bank_transaction_id')->nullable();

            // Fiscal year association
            $table->year('fiscal_year')->nullable();

            // Status and visibility
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->timestamp('archived_at')->nullable();

            // Accounting firm access
            $table->boolean('shared_with_accountant')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Indexes for efficient queries
            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'folder_id']);
            $table->index(['company_id', 'fiscal_year']);
            $table->index(['company_id', 'is_archived']);
            $table->index(['company_id', 'document_date']);
            $table->fullText('ocr_content');

            $table->foreign('bank_transaction_id')->references('id')->on('bank_transactions')->nullOnDelete();
        });

        // Document tags for flexible categorization
        Schema::create('document_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 20)->default('gray');
            $table->timestamps();

            $table->unique(['company_id', 'name']);
        });

        // Pivot table for document-tag relationship
        Schema::create('document_tag', function (Blueprint $table) {
            $table->foreignUuid('document_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('document_tag_id')->constrained()->cascadeOnDelete();

            $table->primary(['document_id', 'document_tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_tag');
        Schema::dropIfExists('document_tags');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_folders');
    }
};
