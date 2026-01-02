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
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('partner_id');
            $table->uuid('invoice_id')->nullable();

            $table->string('credit_note_number', 50)->unique();
            $table->enum('status', ['draft', 'validated', 'sent', 'applied'])->default('draft');

            $table->date('credit_note_date');
            $table->string('reference', 100)->nullable();
            $table->text('reason')->nullable();

            $table->decimal('total_excl_vat', 15, 2)->default(0);
            $table->decimal('total_vat', 15, 2)->default(0);
            $table->decimal('total_incl_vat', 15, 2)->default(0);

            $table->timestamp('validated_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('applied_at')->nullable();

            $table->string('peppol_id')->nullable();
            $table->timestamp('peppol_sent_at')->nullable();

            $table->string('structured_communication', 25)->nullable();

            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('partner_id')->references('id')->on('partners')->onDelete('restrict');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['company_id', 'credit_note_date']);
            $table->index(['company_id', 'status']);
        });

        Schema::create('credit_note_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('credit_note_id');

            $table->integer('line_number');
            $table->text('description');
            $table->decimal('quantity', 15, 4);
            $table->string('unit', 20)->nullable();
            $table->decimal('unit_price', 15, 4);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('line_total', 15, 2);

            $table->string('vat_category', 10)->default('S');
            $table->decimal('vat_rate', 5, 2);
            $table->decimal('vat_amount', 15, 2);

            $table->uuid('account_id')->nullable();

            $table->timestamps();

            $table->foreign('credit_note_id')->references('id')->on('credit_notes')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
        });

        Schema::create('payment_reminders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('invoice_id');

            $table->integer('reminder_level')->default(1);
            $table->date('reminder_date');
            $table->date('due_date');

            $table->enum('status', ['pending', 'sent', 'paid', 'cancelled'])->default('pending');
            $table->enum('send_method', ['email', 'peppol', 'manual'])->default('email');

            $table->decimal('amount_due', 15, 2);
            $table->decimal('late_fee', 15, 2)->default(0);
            $table->decimal('interest_amount', 15, 2)->default(0);

            $table->text('message')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['company_id', 'status']);
            $table->index(['invoice_id', 'reminder_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_reminders');
        Schema::dropIfExists('credit_note_lines');
        Schema::dropIfExists('credit_notes');
    }
};
