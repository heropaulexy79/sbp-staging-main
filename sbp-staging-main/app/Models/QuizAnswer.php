<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizAnswer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'quiz_attempt_id',
        'quiz_question_id',
        'answer',
        'is_correct',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'answer' => 'array',
        'is_correct' => 'boolean',
    ];

    /**
     * Get the quiz attempt that owns the answer.
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }

    /**
     * Get the quiz question that this answer belongs to.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'quiz_question_id');
    }

    /**
     * Get the formatted answer text.
     */
    public function getFormattedAnswer(): string
    {
        if (is_array($this->answer)) {
            return implode(', ', $this->answer);
        }

        return (string) $this->answer;
    }

    /**
     * Get the answer as an array.
     */
    public function getAnswerArray(): array
    {
        if (is_array($this->answer)) {
            return $this->answer;
        }

        return [$this->answer];
    }

    /**
     * Check if the answer is empty.
     */
    public function isEmpty(): bool
    {
        if (is_array($this->answer)) {
            return empty($this->answer) || (count($this->answer) === 1 && empty($this->answer[0]));
        }

        return empty($this->answer);
    }

    /**
     * Get the correct answer for this question.
     */
    public function getCorrectAnswer(): mixed
    {
        return $this->question->getCorrectAnswer();
    }

    /**
     * Get the formatted correct answer text.
     */
    public function getFormattedCorrectAnswer(): string
    {
        $correctAnswer = $this->getCorrectAnswer();

        if (is_array($correctAnswer)) {
            if ($this->question->isMultipleChoice() || $this->question->isTrueFalse()) {
                // For multiple choice, get the option text
                $option = $this->question->options()->find($correctAnswer[0]);
                return $option ? $option->option_text : 'N/A';
            }
            return implode(', ', $correctAnswer);
        }

        return (string) $correctAnswer;
    }

    /**
     * Scope to get correct answers.
     */
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    /**
     * Scope to get incorrect answers.
     */
    public function scopeIncorrect($query)
    {
        return $query->where('is_correct', false);
    }

    /**
     * Scope to get answers for a specific attempt.
     */
    public function scopeForAttempt($query, int $attemptId)
    {
        return $query->where('quiz_attempt_id', $attemptId);
    }

    /**
     * Scope to get answers for a specific question.
     */
    public function scopeForQuestion($query, int $questionId)
    {
        return $query->where('quiz_question_id', $questionId);
    }

    /**
     * Scope to order by question position.
     */
    public function scopeOrderByQuestionPosition($query)
    {
        return $query->join('quiz_questions', 'quiz_answers.quiz_question_id', '=', 'quiz_questions.id')
                    ->orderBy('quiz_questions.position')
                    ->select('quiz_answers.*');
    }
}
