<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('partner_id')->constrained()->cascadeOnDelete();

            $table->enum('type', ['out', 'in']); // out = vente, in = achat
            $table->enum('document_type', ['invoice', 'credit_note', 'debit_note'])->default('invoice');
            $table->enum('status', ['draft', 'validated', 'sent', 'received', 'partial', 'paid', 'cancelled'])->default('draft');

            // Identification
            $table->string('invoice_number', 50);
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->date('delivery_date')->nullable();

            // Reference
            $table->string('reference', 100)->nullable();
            $table->string('order_reference', 100)->nullable();

            // Amounts
            $table->decimal('total_excl_vat', 15, 2)->default(0);
            $table->decimal('total_vat', 15, 2)->default(0);
            $table->decimal('total_incl_vat', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('amount_due', 15, 2)->default(0);
            $table->char('currency', 3)->default('EUR');
            $table->decimal('exchange_rate', 10, 6)->default(1);

            // Peppol
            $table->string('peppol_message_id', 100)->nullable();
            $table->enum('peppol_status', ['pending', 'sent', 'delivered', 'failed', 'received'])->nullable();
            $table->timestamp('peppol_sent_at')->nullable();
            $table->timestamp('peppol_delivered_at')->nullable();
            $table->text('peppol_error')->nullable();

            // Documents
            $table->string('original_file_path')->nullable();
            $table->longText('ubl_xml')->nullable();
            $table->string('pdf_path')->nullable();

            // Accounting
            $table->uuid('journal_entry_id')->nullable();
            $table->boolean('is_booked')->default(false);
            $table->timestamp('booked_at')->nullable();
            $table->foreignUuid('booked_by')->nullable()->constrained('users')->nullOnDelete();

            // Payment
            $table->string('structured_communication', 20)->nullable(); // +++123/4567/89012+++
            $table->string('payment_reference', 100)->nullable();
            $table->string('payment_method', 50)->nullable();

            // Notes
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();

            // Metadata
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'type', 'invoice_number']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'invoice_date']);
            $table->index(['company_id', 'partner_id']);
            $table->index('peppol_message_id');

            $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->nullOnDelete();
        });

        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invoice_id')->constrained()->cascadeOnDelete();
            $table->integer('line_number');

            // Product/Service
            $table->string('product_code', 50)->nullable();
            $table->text('description');
            $table->decimal('quantity', 15, 4)->default(1);
            $table->string('unit_code', 10)->default('C62'); // UN/ECE Recommendation 20
            $table->decimal('unit_price', 15, 4);

            // Discount
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);

            // Amounts
            $table->decimal('line_amount', 15, 2);

            // VAT
            $table->char('vat_category', 2)->default('S'); // S, Z, E, AE, K, G, O
            $table->decimal('vat_rate', 5, 2);
            $table->decimal('vat_amount', 15, 2);

            // Accounting
            $table->uuid('account_id')->nullable();
            $table->uuid('analytic_account_id')->nullable();

            $table->timestamps();

            $table->unique(['invoice_id', 'line_number']);
            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
        });

        // Peppol transmissions log
        Schema::create('peppol_transmissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('invoice_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('direction', ['outbound', 'inbound']);
            $table->string('sender_id', 50);
            $table->string('receiver_id', 50);
            $table->string('document_type', 100);
            $table->string('message_id', 100)->unique()->nullable();

            $table->enum('status', ['pending', 'sent', 'delivered', 'failed']);
            $table->text('error_message')->nullable();

            // AS4 timestamps
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('mdn_received_at')->nullable();

            // Raw data
            $table->longText('request_payload')->nullable();
            $table->longText('response_payload')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'direction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peppol_transmissions');
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoices');
    }
};
