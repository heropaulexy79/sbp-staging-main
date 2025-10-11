<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'course_id', 'slug', 'type', 'content', 'content_json', 'is_published'];

    protected $casts = [
        'content_json' => 'json',
        'is_published' => 'boolean',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user_lesson()
    {
        return $this->hasMany(UserLesson::class);
    }

    /**
     * Get the quiz questions for this lesson.
     */
    public function quizQuestions()
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('position');
    }

    /**
     * Get the quiz attempts for this lesson.
     */
    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function quizWithoutCorrectAnswer()
    {
        $filtered = [];

        foreach ($this->content_json as $quiz) {
            $filtered[] = Arr::except($quiz, ['correct_option']);
        }

        return $filtered;
    }

    /**
     * Check if this lesson has AI-generated quiz questions
     */
    public function hasAiGeneratedQuestions()
    {
        return $this->quizQuestions()->exists();
    }

    const TYPE_DEFAULT = 'DEFAULT';

    const TYPE_QUIZ = 'QUIZ';
}
