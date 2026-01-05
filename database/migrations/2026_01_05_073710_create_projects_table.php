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
        // Projects table
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('partner_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference', 50)->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'planning', 'in_progress', 'on_hold', 'completed', 'cancelled'])->default('draft');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->decimal('actual_cost', 15, 2)->default(0);
            $table->enum('billing_type', ['fixed_price', 'time_materials', 'milestone', 'not_billable'])->default('time_materials');
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->integer('estimated_hours')->nullable();
            $table->integer('progress_percent')->default(0);
            $table->foreignUuid('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('color', 7)->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_template')->default(false);
            $table->foreignUuid('template_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'partner_id']);
        });

        // Project tasks table
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('parent_task_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['todo', 'in_progress', 'review', 'done', 'cancelled'])->default('todo');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('completed_at')->nullable();
            $table->integer('estimated_hours')->nullable();
            $table->decimal('actual_hours', 8, 2)->default(0);
            $table->integer('progress_percent')->default(0);
            $table->foreignUuid('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_milestone')->default(false);
            $table->json('checklist')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'status']);
            $table->index(['assigned_to']);
            $table->foreign('parent_task_id')->references('id')->on('project_tasks')->nullOnDelete();
        });

        // Project members pivot table
        Schema::create('project_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['manager', 'member', 'viewer'])->default('member');
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
        });

        // Timesheets table
        Schema::create('timesheets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('task_id')->nullable()->constrained('project_tasks')->nullOnDelete();
            $table->date('date');
            $table->decimal('hours', 5, 2);
            $table->text('description')->nullable();
            $table->boolean('billable')->default(true);
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->boolean('invoiced')->default(false);
            $table->foreignUuid('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'user_id', 'date']);
            $table->index(['project_id', 'task_id']);
            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheets');
        Schema::dropIfExists('project_members');
        Schema::dropIfExists('project_tasks');
        Schema::dropIfExists('projects');
    }
};
