<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizAttempt extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'lesson_id',
        'user_id',
        'score',
        'total',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'score' => 'integer',
        'total' => 'integer',
    ];

    /**
     * Status enum values.
     */
    public const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const STATUS_COMPLETED = 'COMPLETED';

    /**
     * Get all available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
        ];
    }

    /**
     * Get the lesson that owns the quiz attempt.
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get the user that owns the quiz attempt.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the quiz answers for this attempt.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    /**
     * Get the quiz questions for this attempt's lesson.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'lesson_id', 'lesson_id');
    }

    /**
     * Check if the attempt is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if the attempt is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Get the percentage score.
     */
    public function getPercentageScore(): float
    {
        if ($this->total === 0) {
            return 0;
        }

        return round(($this->score / $this->total) * 100, 2);
    }

    /**
     * Get the grade based on percentage.
     */
    public function getGrade(): string
    {
        $percentage = $this->getPercentageScore();

        if ($percentage >= 90) return 'A';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 60) return 'D';
        return 'F';
    }

    /**
     * Check if the attempt passed (assuming 60% is passing).
     */
    public function isPassed(): bool
    {
        return $this->getPercentageScore() >= 60;
    }

    /**
     * Calculate and update the score.
     */
    public function calculateScore(): void
    {
        $correctAnswers = $this->answers()->where('is_correct', true)->count();
        $totalQuestions = $this->questions()->count();

        $this->update([
            'score' => $correctAnswers,
            'total' => $totalQuestions,
        ]);
    }

    /**
     * Complete the quiz attempt.
     */
    public function complete(): void
    {
        $this->calculateScore();
        $this->update(['status' => self::STATUS_COMPLETED]);
    }

    /**
     * Scope to get attempts by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get completed attempts.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to get in-progress attempts.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Scope to get attempts for a specific lesson.
     */
    public function scopeForLesson($query, int $lessonId)
    {
        return $query->where('lesson_id', $lessonId);
    }

    /**
     * Scope to get attempts by a specific user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to order by score (highest first).
     */
    public function scopeOrderByScore($query, string $direction = 'desc')
    {
        return $query->orderBy('score', $direction);
    }

    /**
     * Scope to order by completion date (most recent first).
     */
    public function scopeOrderByCompleted($query, string $direction = 'desc')
    {
        return $query->orderBy('updated_at', $direction);
    }
}
