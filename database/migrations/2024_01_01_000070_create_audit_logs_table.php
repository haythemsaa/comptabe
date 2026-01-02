<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamp('created_at')->useCurrent();

            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_email', 255)->nullable();
            $table->string('user_role', 50)->nullable();
            $table->foreignUuid('company_id')->nullable()->constrained()->nullOnDelete();

            $table->string('action', 50); // create, read, update, delete, export, login, etc.
            $table->string('resource_type', 100);
            $table->uuid('resource_id')->nullable();

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id', 100)->nullable();

            $table->index('created_at');
            $table->index(['company_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['resource_type', 'resource_id']);
        });

        // Document transmissions (for accountant sync)
        Schema::create('document_transmissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('accountant_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('document_type', 50); // invoice_in, invoice_out, expense, bank_statement
            $table->enum('status', ['pending', 'received', 'processing', 'processed', 'rejected'])->default('pending');

            // Original file
            $table->string('original_file_path')->nullable();
            $table->string('original_filename', 255)->nullable();
            $table->string('original_mime_type', 100)->nullable();

            // OCR extracted data
            $table->json('extracted_data')->nullable();
            $table->decimal('extraction_confidence', 5, 2)->nullable();

            // Proposed accounting entry
            $table->json('proposed_entry')->nullable();

            // Accountant feedback
            $table->enum('accountant_status', ['pending', 'approved', 'modified', 'rejected'])->nullable();
            $table->json('accountant_modifications')->nullable();
            $table->text('accountant_comment')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->foreignUuid('processed_by')->nullable()->constrained('users')->nullOnDelete();

            // Created journal entry
            $table->uuid('journal_entry_id')->nullable();

            $table->timestamp('transmitted_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['accountant_id', 'status']);
            $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->nullOnDelete();
        });

        // Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('document_transmissions');
        Schema::dropIfExists('audit_logs');
    }
};
