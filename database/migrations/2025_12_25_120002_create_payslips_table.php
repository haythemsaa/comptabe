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
        Schema::create('payslips', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('company_id')->constrained()->onDelete('cascade');

            // Period
            $table->string('period', 7); // YYYY-MM format
            $table->integer('year');
            $table->integer('month');
            $table->date('payment_date');

            // Reference
            $table->string('payslip_number', 50)->unique();

            // Working Time
            $table->decimal('worked_hours', 6, 2)->default(0);
            $table->decimal('overtime_hours', 6, 2)->default(0);
            $table->decimal('night_hours', 6, 2)->default(0);
            $table->decimal('weekend_hours', 6, 2)->default(0);
            $table->integer('worked_days')->default(0);

            // Leave
            $table->decimal('paid_leave_days', 5, 2)->default(0);
            $table->decimal('sick_leave_days', 5, 2)->default(0);
            $table->decimal('unpaid_leave_days', 5, 2)->default(0);

            // Gross Salary Components
            $table->decimal('base_salary', 10, 2)->default(0);
            $table->decimal('overtime_pay', 10, 2)->default(0);
            $table->decimal('night_premium', 10, 2)->default(0);
            $table->decimal('weekend_premium', 10, 2)->default(0);
            $table->decimal('bonuses', 10, 2)->default(0);
            $table->decimal('commissions', 10, 2)->default(0);
            $table->decimal('holiday_pay', 10, 2)->default(0); // Pécule de vacances
            $table->decimal('13th_month', 10, 2)->default(0);
            $table->decimal('other_taxable', 10, 2)->default(0);

            // Gross Total
            $table->decimal('gross_total', 10, 2)->default(0);

            // Employee Social Security (ONSS Travailleur)
            $table->decimal('employee_social_security', 10, 2)->default(0);
            $table->decimal('employee_social_security_rate', 5, 2)->default(13.07); // ~13.07%

            // Special Social Security Contribution (Cotisation spéciale)
            $table->decimal('special_social_contribution', 10, 2)->default(0);

            // Professional Withholding Tax (Précompte professionnel)
            $table->decimal('professional_tax', 10, 2)->default(0);
            $table->decimal('professional_tax_rate', 5, 2)->default(0);

            // Other Deductions
            $table->decimal('meal_voucher_deduction', 6, 2)->default(0); // Part employé
            $table->decimal('other_deductions', 10, 2)->default(0);

            // Total Deductions
            $table->decimal('total_deductions', 10, 2)->default(0);

            // Net Salary
            $table->decimal('net_salary', 10, 2)->default(0);

            // Employer Social Security (ONSS Patronale)
            $table->decimal('employer_social_security', 10, 2)->default(0);
            $table->decimal('employer_social_security_rate', 5, 2)->default(25.00); // ~25%

            // Total Cost for Employer
            $table->decimal('total_employer_cost', 10, 2)->default(0);

            // Benefits (Non-cash)
            $table->decimal('company_car_benefit', 10, 2)->default(0);
            $table->integer('meal_vouchers_count')->default(0);
            $table->decimal('meal_vouchers_value', 6, 2)->default(0);
            $table->decimal('eco_vouchers_value', 10, 2)->default(0);

            // Status
            $table->enum('status', ['draft', 'validated', 'paid', 'cancelled'])->default('draft');
            $table->date('validated_at')->nullable();
            $table->foreignUuid('validated_by')->nullable()->constrained('users');

            // PDF Generation
            $table->string('pdf_path')->nullable();
            $table->timestamp('pdf_generated_at')->nullable();

            // Metadata
            $table->json('detailed_items')->nullable(); // Detailed breakdown
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'period']);
            $table->index(['employee_id', 'year', 'month']);
            $table->index('status');
            $table->unique(['employee_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
