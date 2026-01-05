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
        // Drop and recreate the table with composite primary key instead of UUID
        Schema::dropIfExists('project_members');

        Schema::create('project_members', function (Blueprint $table) {
            $table->foreignUuid('project_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['manager', 'member', 'viewer'])->default('member');
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->timestamps();

            $table->primary(['project_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_members');

        Schema::create('project_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['manager', 'member', 'viewer'])->default('member');
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
        });
    }
};
