<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getRoleFormattedAttribute()
    {
        return strtoupper($this->role);
    }

    const ROLE_ADMIN = 'ADMIN';

    const ROLE_MEMBER = 'MEMBER';

    public function isMemberOfOrganisation(Organisation $organisation)
    {
        return $this->organisationNew->organisation->is($organisation); // belong to relationsip
    }

    public function isAdminInOrganisation(Organisation $organisation)
    {
        return $this->isMemberOfOrganisation($organisation) && $this->organisationNew->role === self::ROLE_ADMIN;
    }

    public function organisationNew()
    {
        return $this->hasOne(OrganisationUser::class);
    }
    public function organisation()
    {
        return $this->organisationNew->organisation;
    }

    public function organisationOld()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'course_enrollments');
    }

    public function createdCourses()
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    public function isEnrolledInCourse($courseId)
    {
        return $this->enrolledCourses()->where('course_id', $courseId)->exists();
    }

    public function lessons()
    {
        return $this->hasMany(UserLesson::class, 'user_id', 'id');
    }

    /**
     * Get the quiz attempts for this user.
     */
    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
