<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "auth_code",
        "first_six",
        "last_four",
        "exp_month",
        "exp_year",
        "card_type",
        "bank",
        "country",
        "reusable",
        "account_name",
        "organisation_id",
        "email_address",
    ];


    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}
