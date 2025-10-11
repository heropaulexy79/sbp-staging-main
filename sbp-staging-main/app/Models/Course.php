<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'slug',
        'teacher_id',
        'organisation_id',
        'is_published',
        'banner_image',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    // public function getIsPublishedAttribute($value)
    // {
    //     if ($value === "" || $value === 0) return false;
    //     return true;
    // }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'course_id');
    }

    public function enrolledUsers()
    {
        return $this->belongsToMany(User::class, 'course_enrollments');
    }
}
