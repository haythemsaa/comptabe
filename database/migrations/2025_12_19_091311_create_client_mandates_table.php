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
        Schema::create('client_mandates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('accounting_firm_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();

            // Mandate type
            $table->string('mandate_type', 50)->default('full');
            // full, bookkeeping, tax, payroll, advisory, audit

            // Status
            $table->string('status', 20)->default('active');
            // pending, active, suspended, terminated

            // Period
            $table->date('start_date');
            $table->date('end_date')->nullable();

            // Team assignment
            $table->foreignUuid('manager_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('assigned_users')->nullable();

            // Services included
            $table->json('services')->nullable();

            // Billing
            $table->string('billing_type', 20)->default('monthly');
            // hourly, monthly, annual, package
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('monthly_fee', 10, 2)->nullable();
            $table->decimal('annual_fee', 10, 2)->nullable();

            // Client access
            $table->boolean('client_can_view')->default(true);
            $table->boolean('client_can_edit')->default(false);
            $table->boolean('client_can_validate')->default(false);

            // Notes
            $table->text('internal_notes')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Unique constraint
            $table->unique(['accounting_firm_id', 'company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_mandates');
    }
};
