<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    //

    protected $fillable = [
        'organisation_id',
        'plan',
        'status',
        'billed_at',
        'next_billing_date',
        "description",
        'amount',
        'currency',
        'transaction_ref'
    ];

    protected $casts = [
        'billed_at' => 'datetime',
        'next_billing_date' => 'datetime',
    ];


    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
