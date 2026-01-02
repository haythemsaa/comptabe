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
        Schema::table('expenses', function (Blueprint $table) {
            // AI Suggestions
            $table->json('ai_suggestions')->nullable()->after('notes');

            // AI Categorization
            $table->boolean('ai_categorized')->default(false)->after('ai_suggestions');
            $table->decimal('ai_confidence', 5, 4)->nullable()->after('ai_categorized');
            $table->timestamp('ai_categorized_at')->nullable()->after('ai_confidence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn([
                'ai_suggestions',
                'ai_categorized',
                'ai_confidence',
                'ai_categorized_at',
            ]);
        });
    }
};
