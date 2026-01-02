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
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Backup filename
            $table->string('type'); // database, files, full
            $table->bigInteger('size')->nullable(); // Size in bytes
            $table->string('path'); // Storage path
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->uuid('created_by')->nullable(); // User who created the backup
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->boolean('is_automatic')->default(false); // Auto or manual backup
            $table->integer('retention_days')->default(30); // Days to keep backup
            $table->timestamp('expires_at')->nullable(); // Auto-deletion date
            $table->json('metadata')->nullable(); // Additional info (tables, files count, etc.)
            $table->timestamps();

            $table->index('type');
            $table->index('status');
            $table->index('created_at');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
