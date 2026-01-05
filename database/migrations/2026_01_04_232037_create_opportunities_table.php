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
        Schema::create('opportunities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('partner_id')->nullable()->constrained()->nullOnDelete();

            // Basic info
            $table->string('title');
            $table->text('description')->nullable();

            // Financial
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->integer('probability')->default(50); // 0-100%

            // Pipeline stage
            $table->enum('stage', [
                'lead',           // Nouveau prospect
                'qualified',      // Qualifié
                'proposal',       // Proposition envoyée
                'negotiation',    // En négociation
                'won',            // Gagné
                'lost'            // Perdu
            ])->default('lead');

            // Dates
            $table->date('expected_close_date')->nullable();
            $table->date('actual_close_date')->nullable();

            // Source & Assignment
            $table->string('source', 50)->nullable(); // website, referral, cold_call, event, other
            $table->foreignUuid('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            // If lost
            $table->string('lost_reason')->nullable();

            // Notes
            $table->text('notes')->nullable();

            // AI predictions
            $table->integer('ai_score')->nullable(); // Score IA 0-100
            $table->text('ai_insights')->nullable(); // Insights IA JSON

            // Priority & Tags
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->json('tags')->nullable();

            // Linked quote/invoice
            $table->foreignUuid('quote_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('invoice_id')->nullable()->constrained()->nullOnDelete();

            // Timestamps
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'stage']);
            $table->index(['company_id', 'assigned_to']);
            $table->index(['company_id', 'expected_close_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
