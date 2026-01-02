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
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique(); // e.g., TICK-2025-0001
            $table->uuid('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->uuid('user_id')->nullable(); // User who created the ticket
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->uuid('assigned_to')->nullable(); // Admin assigned to this ticket
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->string('subject');
            $table->text('description');
            $table->enum('status', ['open', 'in_progress', 'waiting_customer', 'resolved', 'closed'])->default('open');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('category', ['technical', 'billing', 'feature_request', 'bug', 'question', 'other'])->default('question');
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('resolution_note')->nullable();
            $table->integer('satisfaction_rating')->nullable(); // 1-5 stars
            $table->text('satisfaction_comment')->nullable();
            $table->timestamps();

            $table->index('ticket_number');
            $table->index('status');
            $table->index('priority');
            $table->index('category');
            $table->index('company_id');
            $table->index('assigned_to');
        });

        // Support ticket messages/replies table
        Schema::create('support_ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained()->cascadeOnDelete();
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->text('message');
            $table->boolean('is_internal_note')->default(false); // Only visible to admins
            $table->json('attachments')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('support_ticket_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_ticket_messages');
        Schema::dropIfExists('support_tickets');
    }
};
