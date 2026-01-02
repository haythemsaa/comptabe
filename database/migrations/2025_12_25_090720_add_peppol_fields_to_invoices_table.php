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
            // Peppol transmission fields
            if (!Schema::hasColumn('invoices', 'peppol_status')) {
                $table->string('peppol_status')->nullable();
            }
            if (!Schema::hasColumn('invoices', 'peppol_transmission_id')) {
                $table->string('peppol_transmission_id')->nullable();
            }
            if (!Schema::hasColumn('invoices', 'peppol_sent_at')) {
                $table->timestamp('peppol_sent_at')->nullable();
            }
            if (!Schema::hasColumn('invoices', 'peppol_received')) {
                $table->boolean('peppol_received')->default(false);
            }
            if (!Schema::hasColumn('invoices', 'peppol_received_at')) {
                $table->timestamp('peppol_received_at')->nullable();
            }
            if (!Schema::hasColumn('invoices', 'ubl_file_path')) {
                $table->string('ubl_file_path')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'peppol_status',
                'peppol_transmission_id',
                'peppol_sent_at',
                'peppol_received',
                'peppol_received_at',
                'ubl_file_path',
            ]);
        });
    }
};
