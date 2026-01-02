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
        Schema::create('recurring_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('partner_id');

            $table->string('name', 100); // e.g., "Abonnement mensuel - Client X"

            // Recurrence settings
            $table->enum('frequency', ['weekly', 'monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->integer('frequency_interval')->default(1); // Every X weeks/months
            $table->integer('day_of_month')->nullable(); // 1-28 for monthly
            $table->integer('day_of_week')->nullable(); // 0-6 for weekly

            // Schedule
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_invoice_date')->nullable();
            $table->date('last_invoice_date')->nullable();
            $table->integer('invoices_generated')->default(0);
            $table->integer('max_invoices')->nullable(); // null = unlimited

            // Invoice template
            $table->integer('payment_terms_days')->default(30);
            $table->text('notes')->nullable();
            $table->string('reference_prefix', 50)->nullable();

            // Amounts (template)
            $table->decimal('total_excl_vat', 15, 2)->default(0);
            $table->decimal('total_vat', 15, 2)->default(0);
            $table->decimal('total_incl_vat', 15, 2)->default(0);
            $table->char('currency', 3)->default('EUR');

            // Status
            $table->enum('status', ['active', 'paused', 'completed', 'cancelled'])->default('active');

            // Options
            $table->boolean('auto_send')->default(false);
            $table->boolean('auto_send_peppol')->default(false);
            $table->boolean('include_structured_communication')->default(true);

            // Tracking
            $table->uuid('created_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('partner_id')->references('id')->on('partners')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'next_invoice_date']);
        });

        Schema::create('recurring_invoice_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('recurring_invoice_id');
            $table->integer('line_number');

            $table->text('description');
            $table->decimal('quantity', 15, 4)->default(1);
            $table->string('unit', 20)->default('pce');
            $table->decimal('unit_price', 15, 4)->default(0);

            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);

            $table->char('vat_category', 1)->default('S');
            $table->decimal('vat_rate', 5, 2)->default(21);
            $table->decimal('vat_amount', 15, 2)->default(0);

            $table->uuid('account_id')->nullable();

            $table->timestamps();

            $table->foreign('recurring_invoice_id')->references('id')->on('recurring_invoices')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->onDelete('set null');

            $table->unique(['recurring_invoice_id', 'line_number']);
        });

        // Track generated invoices
        Schema::create('recurring_invoice_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('recurring_invoice_id');
            $table->uuid('invoice_id');
            $table->date('generated_date');
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('recurring_invoice_id')->references('id')->on('recurring_invoices')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_invoice_history');
        Schema::dropIfExists('recurring_invoice_lines');
        Schema::dropIfExists('recurring_invoices');
    }
};
