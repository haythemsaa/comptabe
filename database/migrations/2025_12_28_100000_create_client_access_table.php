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
        Schema::create('client_access', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->string('access_level')->default('view_only'); // view_only, upload_documents, full_client
            $table->json('permissions')->nullable(); // Permissions granulaires
            $table->timestamp('last_access_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'company_id']);
            $table->index('company_id');
            $table->index('access_level');
        });

        Schema::create('client_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('type'); // invoice, receipt, bank_statement, tax_document, other
            $table->string('category')->nullable();
            $table->string('filename');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->integer('file_size'); // bytes
            $table->string('storage_path');
            $table->text('description')->nullable();
            $table->date('document_date')->nullable();
            $table->foreignUuid('related_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->boolean('is_processed')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('type');
            $table->index('uploaded_by');
        });

        // Comments table already exists from a previous migration, skip creation
        if (!Schema::hasTable('comments')) {
            Schema::create('comments', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuidMorphs('commentable'); // Polymorphic relation (invoices, documents, etc.)
                $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
                $table->text('content');
                $table->json('mentions')->nullable(); // User IDs mentioned with @
                $table->foreignUuid('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
                $table->boolean('is_resolved')->default(false);
                $table->timestamp('resolved_at')->nullable();
                $table->foreignUuid('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index('company_id');
                $table->index('user_id');
                $table->index(['commentable_type', 'commentable_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
        Schema::dropIfExists('client_documents');
        Schema::dropIfExists('client_access');
    }
};
