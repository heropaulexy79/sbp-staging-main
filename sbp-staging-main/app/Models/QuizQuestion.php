<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizQuestion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'lesson_id',
        'question',
        'type',
        'position',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'metadata' => 'array',
        'position' => 'integer',
    ];

    /**
     * Question types enum values.
     */
    public const TYPE_MULTIPLE_CHOICE = 'MULTIPLE_CHOICE';
    public const TYPE_MULTIPLE_SELECT = 'MULTIPLE_SELECT';
    public const TYPE_TRUE_FALSE = 'TRUE_FALSE';
    public const TYPE_TYPE_ANSWER = 'TYPE_ANSWER';
    public const TYPE_PUZZLE = 'PUZZLE';

    /**
     * Get all available question types.
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_MULTIPLE_CHOICE,
            self::TYPE_MULTIPLE_SELECT,
            self::TYPE_TRUE_FALSE,
            self::TYPE_TYPE_ANSWER,
            self::TYPE_PUZZLE,
        ];
    }

    /**
     * Get the lesson that owns the quiz question.
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get the quiz options for the question.
     */
    public function options(): HasMany
    {
        return $this->hasMany(QuizOption::class)->orderBy('position');
    }

    /**
     * Get the quiz answers for this question.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    /**
     * Get the correct options for this question.
     */
    public function correctOptions(): HasMany
    {
        return $this->hasMany(QuizOption::class)->where('is_correct', true);
    }

    /**
     * Check if this is a multiple choice question.
     */
    public function isMultipleChoice(): bool
    {
        return $this->type === self::TYPE_MULTIPLE_CHOICE;
    }

    /**
     * Check if this is a multiple select question.
     */
    public function isMultipleSelect(): bool
    {
        return $this->type === self::TYPE_MULTIPLE_SELECT;
    }

    /**
     * Check if this is a true/false question.
     */
    public function isTrueFalse(): bool
    {
        return $this->type === self::TYPE_TRUE_FALSE;
    }

    /**
     * Check if this is a type answer question.
     */
    public function isTypeAnswer(): bool
    {
        return $this->type === self::TYPE_TYPE_ANSWER;
    }

    /**
     * Check if this is a puzzle question.
     */
    public function isPuzzle(): bool
    {
        return $this->type === self::TYPE_PUZZLE;
    }

    /**
     * Get the correct answer(s) for this question.
     */
    public function getCorrectAnswer(): mixed
    {
        if ($this->isTypeAnswer() || $this->isPuzzle()) {
            return $this->metadata['correct_answer'] ?? null;
        }

        return $this->correctOptions->pluck('id')->toArray();
    }

    /**
     * Check if the given answer is correct.
     */
    public function isAnswerCorrect(mixed $answer): bool
    {
        $correctAnswer = $this->getCorrectAnswer();

        if ($this->isTypeAnswer() || $this->isPuzzle()) {
            return strtolower(trim($answer)) === strtolower(trim($correctAnswer));
        }

        if ($this->isMultipleSelect()) {
            return is_array($answer) && 
                   count($answer) === count($correctAnswer) && 
                   empty(array_diff($answer, $correctAnswer));
        }

        // Multiple choice or true/false
        return is_array($answer) && count($answer) === 1 && $answer[0] == $correctAnswer[0];
    }

    /**
     * Scope to order questions by position.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    /**
     * Scope to filter by question type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
