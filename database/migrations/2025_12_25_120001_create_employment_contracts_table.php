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
        Schema::create('employment_contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained()->onDelete('cascade');

            // Contract Details
            $table->string('contract_number', 50)->unique();
            $table->enum('contract_type', [
                'cdi',              // Contrat à durée indéterminée
                'cdd',              // Contrat à durée déterminée
                'interim',          // Intérim
                'student',          // Étudiant
                'apprenticeship',   // Apprentissage
                'flexi',            // Flexi-job
                'extra',            // Extra
            ])->default('cdi');

            $table->enum('work_regime', ['full_time', 'part_time'])->default('full_time');
            $table->decimal('weekly_hours', 5, 2)->default(38.00); // Heures/semaine

            // Period
            $table->date('start_date');
            $table->date('end_date')->nullable(); // Null for CDI
            $table->date('trial_period_end')->nullable();

            // Function
            $table->string('job_title', 100);
            $table->string('job_category', 100)->nullable(); // CP 200, etc.
            $table->integer('paritair_committee')->nullable(); // Commission paritaire
            $table->text('job_description')->nullable();

            // Compensation
            $table->decimal('gross_monthly_salary', 10, 2);
            $table->decimal('gross_hourly_rate', 8, 2)->nullable();

            // Benefits (Avantages)
            $table->boolean('company_car')->default(false);
            $table->decimal('company_car_value', 10, 2)->nullable();
            $table->boolean('meal_vouchers')->default(false);
            $table->decimal('meal_voucher_value', 5, 2)->nullable();
            $table->boolean('eco_vouchers')->default(false);
            $table->decimal('eco_voucher_value', 10, 2)->nullable();
            $table->boolean('group_insurance')->default(false);
            $table->boolean('hospitalization_insurance')->default(false);
            $table->boolean('mobile_phone')->default(false);
            $table->boolean('internet_allowance')->default(false);
            $table->decimal('internet_allowance_amount', 6, 2)->nullable();

            // Bonuses
            $table->decimal('13th_month', 10, 2)->nullable();
            $table->decimal('year_end_bonus', 10, 2)->nullable();

            // Leave Entitlement
            $table->integer('annual_leave_days')->default(20);
            $table->integer('extra_legal_days')->default(0);

            // Work Location
            $table->string('work_location', 200)->nullable();
            $table->boolean('remote_work_allowed')->default(false);
            $table->integer('remote_days_per_week')->default(0);

            // Status
            $table->enum('status', ['draft', 'active', 'expired', 'terminated'])->default('draft');
            $table->date('signature_date')->nullable();
            $table->string('signed_document_path')->nullable();

            // Metadata
            $table->json('additional_clauses')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['employee_id', 'status']);
            $table->index('start_date');
            $table->index('contract_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employment_contracts');
    }
};
