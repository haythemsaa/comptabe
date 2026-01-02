<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('type', 50);
            $table->text('description')->nullable();
            $table->json('config')->nullable();
            $table->json('filters')->nullable();
            $table->json('schedule')->nullable();
            $table->timestamp('last_generated_at')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'user_id']);
            $table->index('is_favorite');
        });

        Schema::create('report_executions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('pending');
            $table->string('format', 10)->default('pdf');
            $table->json('parameters')->nullable();
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('execution_time_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['report_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_executions');
        Schema::dropIfExists('reports');
    }
};
