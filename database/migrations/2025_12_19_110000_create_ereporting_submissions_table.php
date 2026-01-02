<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('e_reporting_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('invoice_id')->nullable()->constrained()->onDelete('set null');
            $table->string('submission_id')->unique();
            $table->enum('type', ['sales', 'purchases', 'correction'])->default('sales');
            $table->enum('status', ['pending', 'submitted', 'accepted', 'rejected', 'error'])->default('pending');
            $table->string('government_reference')->nullable();
            $table->text('request_payload')->nullable();
            $table->text('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'type']);
            $table->index('submitted_at');
        });

        // Add e-Reporting fields to companies
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('ereporting_enabled')->default(false)->after('peppol_test_mode');
            $table->boolean('ereporting_test_mode')->default(true)->after('ereporting_enabled');
            $table->string('ereporting_api_key')->nullable()->after('ereporting_test_mode');
            $table->string('ereporting_certificate_id')->nullable()->after('ereporting_api_key');
        });

        // Add e-Reporting fields to invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('ereporting_status')->nullable()->after('peppol_error');
            $table->timestamp('ereporting_submitted_at')->nullable()->after('ereporting_status');
            $table->string('ereporting_reference')->nullable()->after('ereporting_submitted_at');
            $table->text('ereporting_error')->nullable()->after('ereporting_reference');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'ereporting_status',
                'ereporting_submitted_at',
                'ereporting_reference',
                'ereporting_error',
            ]);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'ereporting_enabled',
                'ereporting_test_mode',
                'ereporting_api_key',
                'ereporting_certificate_id',
            ]);
        });

        Schema::dropIfExists('e_reporting_submissions');
    }
};
