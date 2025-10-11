<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrganisationInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'organisation_id',
        'token',
        'role',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public static function generateUniqueToken()
    {
        // Generate a unique random token (e.g., using Str::random())
        $token = Str::random(60);
        while (OrganisationInvitation::where('token', $token)->exists()) {
            $token = Str::random(60);
        }

        return $token;
    }

    public function getRoleFormattedAttribute()
    {
        return strtoupper($this->role);
    }
}
