<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizOption extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'quiz_question_id',
        'option_text',
        'is_correct',
        'position',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_correct' => 'boolean',
        'position' => 'integer',
    ];

    /**
     * Get the quiz question that owns the option.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'quiz_question_id');
    }

    /**
     * Scope to order options by position.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    /**
     * Scope to get only correct options.
     */
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    /**
     * Scope to get only incorrect options.
     */
    public function scopeIncorrect($query)
    {
        return $query->where('is_correct', false);
    }
}
