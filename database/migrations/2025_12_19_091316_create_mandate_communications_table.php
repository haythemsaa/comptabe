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
        Schema::create('mandate_communications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_mandate_id')->constrained()->cascadeOnDelete();

            // Sender
            $table->foreignUuid('sender_id')->constrained('users');
            $table->string('sender_type', 20); // cabinet, client

            // Message
            $table->string('subject')->nullable();
            $table->text('message');

            // Attachments
            $table->json('attachments')->nullable();

            // Status
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();

            // Reply to
            $table->foreignUuid('parent_id')->nullable();

            // Timestamp
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index(['client_mandate_id', 'created_at']);
            $table->index(['client_mandate_id', 'is_read']);

            // Self-reference for replies (added separately to avoid issues)
            $table->foreign('parent_id')->references('id')->on('mandate_communications')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mandate_communications');
    }
};
