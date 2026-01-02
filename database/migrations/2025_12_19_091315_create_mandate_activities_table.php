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
        Schema::create('mandate_activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_mandate_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained();

            // Activity type
            $table->string('activity_type', 50);
            // login, invoice_created, vat_declared, document_uploaded, note_added, etc.

            // Description
            $table->text('description')->nullable();

            // Metadata
            $table->json('metadata')->nullable();

            // Time tracking (for billing)
            $table->unsignedInteger('time_spent_minutes')->nullable();
            $table->boolean('is_billable')->default(false);

            // Timestamp
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index(['client_mandate_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('activity_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mandate_activities');
    }
};
