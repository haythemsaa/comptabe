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
        // Reminder configurations per company
        Schema::create('invoice_reminder_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->onDelete('cascade');

            // Enable/Disable
            $table->boolean('is_enabled')->default(false);

            // First Reminder (before due date)
            $table->boolean('send_before_due')->default(true);
            $table->integer('days_before_due')->default(7); // 7 days before

            // Second Reminder (on due date)
            $table->boolean('send_on_due_date')->default(true);

            // Third Reminder (after due date - first overdue)
            $table->boolean('send_first_overdue')->default(true);
            $table->integer('days_after_due_first')->default(7); // 7 days after

            // Fourth Reminder (second overdue)
            $table->boolean('send_second_overdue')->default(true);
            $table->integer('days_after_due_second')->default(15); // 15 days after

            // Fifth Reminder (third overdue - final)
            $table->boolean('send_final_reminder')->default(true);
            $table->integer('days_after_due_final')->default(30); // 30 days after

            // Late Fee Settings
            $table->boolean('apply_late_fees')->default(false);
            $table->decimal('late_fee_percentage', 5, 2)->nullable(); // e.g., 2.00 for 2%
            $table->decimal('late_fee_fixed', 10, 2)->nullable(); // Fixed amount

            // Interest Settings (Belgian legal: 10% per year)
            $table->boolean('apply_interest')->default(false);
            $table->decimal('interest_rate_annual', 5, 2)->default(10.00); // 10% Belgian legal rate

            // Email Templates
            $table->text('email_subject_before_due')->nullable();
            $table->text('email_body_before_due')->nullable();
            $table->text('email_subject_on_due')->nullable();
            $table->text('email_body_on_due')->nullable();
            $table->text('email_subject_overdue')->nullable();
            $table->text('email_body_overdue')->nullable();

            // Exclusions
            $table->json('excluded_partner_ids')->nullable(); // Don't send reminders to these partners

            $table->timestamps();

            $table->unique('company_id');
        });

        // Reminder history
        Schema::create('invoice_reminders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('company_id')->constrained()->onDelete('cascade');

            // Reminder Info
            $table->enum('reminder_type', [
                'before_due',
                'on_due_date',
                'first_overdue',
                'second_overdue',
                'final_reminder',
                'manual',
            ]);
            $table->integer('reminder_number'); // 1, 2, 3, etc.

            // Dates
            $table->date('sent_date');
            $table->date('invoice_due_date');
            $table->integer('days_overdue')->default(0); // Negative if before due

            // Email Details
            $table->string('recipient_email');
            $table->string('email_subject');
            $table->text('email_body');
            $table->boolean('email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            $table->text('email_error')->nullable();

            // Amounts
            $table->decimal('invoice_amount', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('amount_due', 10, 2);
            $table->decimal('late_fee_applied', 10, 2)->default(0);
            $table->decimal('interest_applied', 10, 2)->default(0);

            // Response tracking
            $table->boolean('opened')->default(false);
            $table->timestamp('opened_at')->nullable();
            $table->boolean('payment_received')->default(false);
            $table->timestamp('payment_received_at')->nullable();

            $table->json('metadata')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('invoice_id');
            $table->index(['company_id', 'sent_date']);
            $table->index('reminder_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_reminders');
        Schema::dropIfExists('invoice_reminder_configs');
    }
};
