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
        Schema::create('peppol_usage', function (Blueprint $table) {
            $table->id();
            $table->char('company_id', 36);
            $table->char('invoice_id', 36)->nullable();
            $table->enum('action', ['send', 'receive']); // send or receive
            $table->enum('document_type', ['invoice', 'credit_note', 'debit_note'])->default('invoice');
            $table->string('transmission_id')->nullable();
            $table->string('participant_id')->nullable(); // recipient or sender
            $table->enum('status', ['success', 'failed', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            $table->decimal('cost', 8, 4)->default(0); // cost per transaction
            $table->boolean('counted_in_quota')->default(true);
            $table->integer('month')->index(); // month number (1-12)
            $table->integer('year')->index(); // year (2025, 2026, etc.)
            $table->timestamps();

            // Foreign keys
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');

            // Indexes for reporting
            $table->index(['company_id', 'year', 'month']);
            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peppol_usage');
    }
};
