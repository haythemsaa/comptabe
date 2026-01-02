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
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignUuid('invoice_template_id')
                ->nullable()
                ->after('company_id')
                ->constrained('invoice_templates')
                ->onDelete('set null');

            $table->foreignUuid('recurring_invoice_id')
                ->nullable()
                ->after('invoice_template_id')
                ->constrained('recurring_invoices')
                ->onDelete('set null');

            $table->index('invoice_template_id');
            $table->index('recurring_invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['invoice_template_id']);
            $table->dropForeign(['recurring_invoice_id']);
            $table->dropColumn(['invoice_template_id', 'recurring_invoice_id']);
        });
    }
};
