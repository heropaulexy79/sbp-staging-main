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
        // Knowledge base table for RAG (MySQL - main content storage)
        Schema::create('knowledge_base', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('source_type', ['course', 'lesson', 'quiz', 'external']);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->text('keywords')->nullable(); // comma-separated keywords for search
            $table->timestamps();
            
            $table->index(['source_type', 'source_id']);
            $table->index('title');
        });

        // AI generation history
        Schema::create('ai_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();
            $table->text('prompt');
            $table->text('generated_content');
            $table->string('model_used', 100)->default('gemini-1.5-flash');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_generations');
        Schema::dropIfExists('knowledge_base');
    }
};


