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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Payment provider information
            $table->string('payment_provider')->nullable()->after('billing_cycle'); // mollie, stripe
            $table->string('provider_subscription_id')->nullable()->after('payment_provider');
            $table->string('provider_customer_id')->nullable()->after('provider_subscription_id');

            // Next payment date
            $table->date('next_payment_date')->nullable()->after('current_period_end');
        });

        Schema::table('subscription_invoices', function (Blueprint $table) {
            // Payment provider information for invoices
            $table->string('payment_provider')->nullable()->after('payment_method');
            $table->string('provider_payment_id')->nullable()->after('payment_provider');
            $table->string('provider_invoice_id')->nullable()->after('provider_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'payment_provider',
                'provider_subscription_id',
                'provider_customer_id',
                'next_payment_date',
            ]);
        });

        Schema::table('subscription_invoices', function (Blueprint $table) {
            $table->dropColumn([
                'payment_provider',
                'provider_payment_id',
                'provider_invoice_id',
            ]);
        });
    }
};
