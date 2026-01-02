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
        // Payment Methods (stored payment methods like cards, SEPA mandates)
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();

            $table->string('provider'); // mollie, stripe
            $table->string('provider_method_id'); // Provider's method ID
            $table->string('type'); // card, sepa_debit, bancontact, etc.

            // Card/Bank details (masked for display)
            $table->string('last_four')->nullable();
            $table->string('brand')->nullable(); // visa, mastercard, etc.
            $table->string('bank_name')->nullable();
            $table->string('holder_name')->nullable();
            $table->integer('exp_month')->nullable();
            $table->integer('exp_year')->nullable();

            $table->boolean('is_default')->default(false);
            $table->boolean('is_verified')->default(false);

            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_default']);
        });

        // Payment Transactions (detailed log of all payment events)
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('subscription_invoice_id')->nullable()->constrained()->nullOnDelete();

            $table->string('provider'); // mollie, stripe
            $table->string('provider_payment_id')->nullable();
            $table->string('provider_refund_id')->nullable();

            $table->string('type'); // payment, refund, chargeback
            $table->string('status'); // pending, paid, failed, refunded, etc.

            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->decimal('fee', 10, 2)->default(0); // Provider fee
            $table->decimal('net_amount', 10, 2); // Amount minus fee

            $table->string('payment_method')->nullable(); // card, sepa_debit, etc.
            $table->foreignUuid('payment_method_id')->nullable()->constrained()->nullOnDelete();

            $table->string('description')->nullable();
            $table->text('metadata')->nullable(); // JSON metadata
            $table->text('error_message')->nullable();
            $table->string('failure_reason')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['provider', 'provider_payment_id']);
            $table->index('subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('payment_methods');
    }
};
