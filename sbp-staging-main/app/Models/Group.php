<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'organisation_id',
    ];

    protected $appends = [
        'users_count',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'group_users');
    }

    public function getUsersCountAttribute()
    {
        // If the users relationship is already loaded, use it
        if ($this->relationLoaded('users')) {
            return $this->users->count();
        }
        
        // Otherwise, make a query
        return $this->users()->count();
    }
}
