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
        Schema::create('activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();

            // Polymorphic relation (opportunity, partner, invoice, quote)
            $table->string('related_type', 50); // opportunity, partner, invoice, quote
            $table->uuid('related_id');

            // Activity type
            $table->enum('type', [
                'call',      // Appel téléphonique
                'email',     // Email
                'meeting',   // Réunion
                'note',      // Note interne
                'task',      // Tâche à faire
                'demo',      // Démonstration
                'follow_up'  // Suivi
            ]);

            // Content
            $table->string('subject');
            $table->text('description')->nullable();

            // Scheduling
            $table->datetime('due_date')->nullable();
            $table->datetime('start_date')->nullable();
            $table->integer('duration')->nullable(); // minutes

            // Completion
            $table->datetime('completed_at')->nullable();
            $table->text('outcome')->nullable(); // Résultat de l'activité

            // Assignment
            $table->foreignUuid('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Priority
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');

            // Reminders
            $table->boolean('has_reminder')->default(false);
            $table->datetime('reminder_at')->nullable();
            $table->boolean('reminder_sent')->default(false);

            // For emails
            $table->string('email_to')->nullable();
            $table->string('email_cc')->nullable();

            // For calls
            $table->string('phone_number')->nullable();
            $table->enum('call_direction', ['incoming', 'outgoing'])->nullable();
            $table->enum('call_result', ['answered', 'no_answer', 'busy', 'voicemail'])->nullable();

            // For meetings
            $table->string('location')->nullable();
            $table->string('meeting_url')->nullable(); // Teams, Zoom, etc.
            $table->json('attendees')->nullable(); // Liste participants

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'related_type', 'related_id']);
            $table->index(['company_id', 'assigned_to', 'completed_at']);
            $table->index(['company_id', 'due_date']);
            $table->index(['company_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
