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
        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('partner_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20); // income, expense
            $table->string('description');
            $table->text('notes')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('frequency', 20); // daily, weekly, monthly, quarterly, yearly
            $table->integer('interval')->default(1); // every X frequency
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_occurrence_date');
            $table->string('category', 50)->nullable();
            $table->string('account_code', 20)->nullable();
            $table->string('vat_code', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_create')->default(false);
            $table->integer('occurrences_count')->default(0);
            $table->timestamp('last_executed_at')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('next_occurrence_date');
            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_transactions');
    }
};
