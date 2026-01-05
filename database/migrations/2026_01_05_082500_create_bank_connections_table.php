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
        Schema::create('bank_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->string('bank_id')->nullable();
            $table->string('bank_name');
            $table->string('bic')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamp('consent_expires_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->enum('status', ['pending', 'active', 'expired', 'error'])->default('pending');
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_connections');
    }
};
