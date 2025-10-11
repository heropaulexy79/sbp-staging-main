<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisationUser extends Model
{
    use HasFactory;

    protected $fillable = ["user_id", "organisation_id", "role"];


    public function organisation()
    {
        return $this->belongsTo(Organisation::class, "organisation_id");
    }

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
}
