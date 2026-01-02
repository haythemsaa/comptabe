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
        Schema::create('quotes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('partner_id');

            $table->string('quote_number', 50);
            $table->date('quote_date');
            $table->date('valid_until')->nullable();

            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired', 'converted'])->default('draft');

            // Amounts
            $table->decimal('total_excl_vat', 15, 2)->default(0);
            $table->decimal('total_vat', 15, 2)->default(0);
            $table->decimal('total_incl_vat', 15, 2)->default(0);
            $table->char('currency', 3)->default('EUR');

            // Discount
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);

            // Reference & notes
            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();

            // Conversion to invoice
            $table->uuid('converted_invoice_id')->nullable();
            $table->timestamp('converted_at')->nullable();

            // Tracking
            $table->uuid('created_by')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('partner_id')->references('id')->on('partners')->onDelete('cascade');
            $table->foreign('converted_invoice_id')->references('id')->on('invoices')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->unique(['company_id', 'quote_number']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'quote_date']);
        });

        Schema::create('quote_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('quote_id');
            $table->integer('line_number');

            $table->text('description');
            $table->decimal('quantity', 15, 4)->default(1);
            $table->string('unit', 20)->default('pce');
            $table->decimal('unit_price', 15, 4)->default(0);

            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);

            $table->decimal('line_total', 15, 2)->default(0);

            $table->char('vat_category', 1)->default('S');
            $table->decimal('vat_rate', 5, 2)->default(21);
            $table->decimal('vat_amount', 15, 2)->default(0);

            $table->uuid('account_id')->nullable();

            $table->timestamps();

            $table->foreign('quote_id')->references('id')->on('quotes')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->onDelete('set null');

            $table->unique(['quote_id', 'line_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_lines');
        Schema::dropIfExists('quotes');
    }
};
