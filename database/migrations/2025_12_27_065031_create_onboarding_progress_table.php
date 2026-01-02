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
        Schema::create('onboarding_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('company_id')->nullable()->constrained('companies')->cascadeOnDelete();

            // Survey responses
            $table->string('role')->nullable(); // freelance, tpe, pme, comptable
            $table->json('goals')->nullable(); // ['facturation', 'suivi_depenses', 'conformite_fiscale']
            $table->string('experience_level')->nullable(); // debutant, intermediaire, expert

            // Checklist completion
            $table->json('completed_steps')->nullable(); // ['profile_completed', 'first_invoice', ...]
            $table->integer('progress_percentage')->default(0);

            // Tour progress
            $table->boolean('tour_completed')->default(false);
            $table->json('tour_steps_seen')->nullable();
            $table->timestamp('tour_started_at')->nullable();
            $table->timestamp('tour_completed_at')->nullable();

            // General flags
            $table->boolean('onboarding_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->boolean('skipped')->default(false);

            // Metadata (for tracking sent emails, etc.)
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->unique('user_id');
            $table->index(['company_id', 'onboarding_completed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onboarding_progress');
    }
};
