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
        Schema::create('chat_tool_executions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('message_id');
            $table->string('tool_name'); // e.g., 'create_invoice', 'invite_user'
            $table->json('tool_input'); // Parameters sent to tool
            $table->json('tool_output')->nullable(); // Result from tool execution
            $table->enum('status', ['pending', 'success', 'error'])->default('pending');
            $table->text('error_message')->nullable();
            $table->boolean('requires_confirmation')->default(false);
            $table->boolean('confirmed')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->foreign('message_id')->references('id')->on('chat_messages')->onDelete('cascade');
            $table->index(['message_id', 'tool_name']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_tool_executions');
    }
};
