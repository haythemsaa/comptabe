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
        Schema::create('mandate_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_mandate_id')->constrained()->cascadeOnDelete();

            // Task info
            $table->string('title');
            $table->text('description')->nullable();

            // Type
            $table->string('task_type', 50);
            // vat_declaration, annual_accounts, tax_return, bookkeeping, payroll, meeting, other

            // Period
            $table->integer('fiscal_year')->nullable();
            $table->string('period', 20)->nullable();

            // Deadlines
            $table->date('due_date')->nullable();
            $table->date('reminder_date')->nullable();

            // Assignment
            $table->foreignUuid('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            // Status
            $table->string('status', 20)->default('pending');
            // pending, in_progress, review, completed, cancelled
            $table->string('priority', 10)->default('normal');
            // low, normal, high, urgent

            // Time tracking
            $table->decimal('estimated_hours', 5, 2)->nullable();
            $table->decimal('actual_hours', 5, 2)->nullable();

            // Billing
            $table->boolean('is_billable')->default(true);
            $table->timestamp('billed_at')->nullable();

            // Completion
            $table->timestamp('completed_at')->nullable();
            $table->foreignUuid('completed_by')->nullable()->constrained('users')->nullOnDelete();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['client_mandate_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mandate_tasks');
    }
};
