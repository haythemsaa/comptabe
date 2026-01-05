<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add employee expense fields to existing expense_categories table
        if (Schema::hasTable('expense_categories') && !Schema::hasColumn('expense_categories', 'max_amount')) {
            Schema::table('expense_categories', function (Blueprint $table) {
                $table->decimal('default_vat_rate', 5, 2)->default(21.00)->after('account_code');
                $table->decimal('max_amount', 12, 2)->nullable()->after('default_vat_rate');
                $table->boolean('requires_receipt')->default(true)->after('max_amount');
                $table->boolean('requires_approval')->default(true)->after('requires_receipt');
                $table->boolean('is_mileage')->default(false)->after('requires_approval');
                $table->decimal('mileage_rate', 8, 4)->nullable()->after('is_mileage');
            });
        }

        // Expense Reports (Employee expense reports / Rapports de frais)
        Schema::create('expense_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 50); // NDF-2026-0001
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'approved',
                'rejected',
                'paid',
                'cancelled'
            ])->default('draft');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('total_vat', 12, 2)->default(0);
            $table->decimal('approved_amount', 12, 2)->nullable();
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignUuid('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'user_id']);
        });

        // Employee Expenses (Notes de frais employés)
        Schema::create('employee_expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('expense_report_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->date('expense_date');
            $table->string('merchant')->nullable();
            $table->text('description');
            $table->decimal('amount', 12, 2);
            $table->decimal('vat_amount', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2);
            $table->decimal('vat_rate', 5, 2)->default(21.00);
            $table->string('currency', 3)->default('EUR');
            $table->decimal('exchange_rate', 10, 6)->default(1);
            $table->decimal('amount_eur', 12, 2)->nullable();
            $table->enum('payment_method', [
                'company_card',
                'personal_card',
                'cash',
                'bank_transfer',
                'other'
            ])->default('personal_card');
            $table->boolean('is_billable')->default(false);
            $table->foreignUuid('project_id')->nullable();
            $table->foreignUuid('partner_id')->nullable();
            $table->enum('status', [
                'draft',
                'pending',
                'approved',
                'rejected',
                'reimbursed'
            ])->default('draft');
            // Mileage
            $table->boolean('is_mileage')->default(false);
            $table->decimal('distance_km', 10, 2)->nullable();
            $table->string('departure')->nullable();
            $table->string('destination')->nullable();
            $table->string('vehicle_type')->nullable();
            // Receipt
            $table->string('receipt_path')->nullable();
            $table->string('receipt_original_name')->nullable();
            $table->boolean('has_receipt')->default(false);
            // OCR
            $table->json('ocr_data')->nullable();
            $table->boolean('ocr_processed')->default(false);
            // Accounting
            $table->string('accounting_code', 20)->nullable();
            $table->boolean('is_booked')->default(false);
            $table->foreignUuid('accounting_entry_id')->nullable();
            // Notes
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'user_id']);
            $table->index(['company_id', 'expense_date']);
        });

        // Expense Attachments
        Schema::create('expense_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_expense_id')->constrained('employee_expenses')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->integer('file_size');
            $table->enum('type', ['receipt', 'invoice', 'justification', 'other'])->default('receipt');
            $table->timestamps();
        });

        // Expense Policies
        Schema::create('expense_policies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignUuid('category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->decimal('daily_limit', 12, 2)->nullable();
            $table->decimal('monthly_limit', 12, 2)->nullable();
            $table->decimal('per_expense_limit', 12, 2)->nullable();
            $table->boolean('requires_pre_approval')->default(false);
            $table->decimal('pre_approval_threshold', 12, 2)->nullable();
            $table->boolean('auto_approve_below')->default(false);
            $table->decimal('auto_approve_threshold', 12, 2)->nullable();
            $table->json('allowed_payment_methods')->nullable();
            $table->boolean('receipt_required')->default(true);
            $table->decimal('receipt_required_threshold', 12, 2)->default(25);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_policies');
        Schema::dropIfExists('expense_attachments');
        Schema::dropIfExists('employee_expenses');
        Schema::dropIfExists('expense_reports');

        // Remove added columns from expense_categories
        if (Schema::hasTable('expense_categories') && Schema::hasColumn('expense_categories', 'max_amount')) {
            Schema::table('expense_categories', function (Blueprint $table) {
                $table->dropColumn([
                    'default_vat_rate',
                    'max_amount',
                    'requires_receipt',
                    'requires_approval',
                    'is_mileage',
                    'mileage_rate',
                ]);
            });
        }
    }
};
