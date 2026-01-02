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
        Schema::create('system_errors', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->uuid('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();

            $table->string('severity', 20)->default('error'); // error, warning, critical
            $table->string('type', 100); // exception, validation, database, api, etc.
            $table->string('message');
            $table->text('exception')->nullable(); // Exception class name
            $table->text('file')->nullable();
            $table->integer('line')->nullable();
            $table->longText('trace')->nullable(); // Stack trace
            $table->text('url')->nullable();
            $table->string('method', 10)->nullable(); // GET, POST, etc.
            $table->text('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('context')->nullable(); // Additional context data
            $table->json('request_data')->nullable(); // Request payload
            $table->integer('occurrences')->default(1); // Count duplicate errors
            $table->timestamp('last_occurred_at')->nullable();
            $table->boolean('resolved')->default(false);
            $table->uuid('resolved_by')->nullable();
            $table->foreign('resolved_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_note')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('company_id');
            $table->index('severity');
            $table->index('type');
            $table->index('resolved');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_errors');
    }
};
