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
        // Opportunities table - Pipeline de ventes
        Schema::create('opportunities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('partner_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->integer('probability')->default(50); // 0-100%
            $table->enum('stage', [
                'lead',           // Prospect
                'qualified',      // Qualifié
                'proposal',       // Proposition envoyée
                'negotiation',    // En négociation
                'won',            // Gagné
                'lost'            // Perdu
            ])->default('lead');
            $table->date('expected_close_date')->nullable();
            $table->date('actual_close_date')->nullable();
            $table->string('source', 100)->nullable(); // website, referral, cold_call, event, etc.
            $table->foreignUuid('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('lost_reason')->nullable();
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->integer('sort_order')->default(0); // Pour le tri dans le Kanban
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'stage']);
            $table->index(['company_id', 'assigned_to']);
            $table->index(['company_id', 'expected_close_date']);
        });

        // Activities table - Historique des interactions
        Schema::create('activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->string('related_type', 100); // App\Models\Opportunity, App\Models\Partner, etc.
            $table->uuid('related_id');
            $table->enum('type', [
                'call',       // Appel téléphonique
                'email',      // Email
                'meeting',    // Réunion
                'note',       // Note interne
                'task',       // Tâche à faire
                'demo',       // Démonstration
                'follow_up'   // Relance
            ]);
            $table->string('subject');
            $table->text('description')->nullable();
            $table->datetime('due_date')->nullable();
            $table->integer('duration')->nullable(); // minutes
            $table->boolean('is_completed')->default(false);
            $table->datetime('completed_at')->nullable();
            $table->foreignUuid('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->json('metadata')->nullable(); // Données supplémentaires (numéro tel, email, etc.)
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'related_type', 'related_id']);
            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'due_date']);
            $table->index(['assigned_to', 'is_completed']);
        });

        // Opportunity stage history - Historique des changements d'étape
        Schema::create('opportunity_stage_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('opportunity_id')->constrained()->cascadeOnDelete();
            $table->string('from_stage')->nullable();
            $table->string('to_stage');
            $table->foreignUuid('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['opportunity_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunity_stage_history');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('opportunities');
    }
};
