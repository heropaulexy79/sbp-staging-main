<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLesson extends Model
{
    use HasFactory;

    public $timestamps = false; // Disable timestamps for pivot table

    // This breaks something // just leave as default
    // protected $primaryKey = ['user_id', 'lesson_id'];

    protected $fillable = [
        'user_id',
        'lesson_id',
        'completed',
        'score',
        'answers',
    ];

    protected $casts = [
        'answers' => 'json'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
