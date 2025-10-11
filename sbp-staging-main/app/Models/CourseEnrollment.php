<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_completed',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];
}


//    public function getCourseProgressAttribute($course)
//     {
//         $enrolledCourse = $this->enrolledCourses()->where('course_id', $course->id)->first();

//         if (!$enrolledCourse) {
//             return 0; // User might not be enrolled in this course
//         }

//         $totalLessons = $course->lessons->where('is_published', '1')->count();
//         $completedLessons = $enrolledCourse->userLessons()->completed()->count();

//         return round(($completedLessons / $totalLessons) * 100, 2); // Calculate percentage with 2 decimals
//     }
