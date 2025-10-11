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
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
            $table->foreignId('quiz_question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->json('answer')->nullable(); // Can hold text or option IDs
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['quiz_attempt_id', 'quiz_question_id']);
            $table->index('is_correct');
            
            // Ensure one answer per question per attempt
            $table->unique(['quiz_attempt_id', 'quiz_question_id'], 'unique_answer_per_question');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_answers');
    }
};
