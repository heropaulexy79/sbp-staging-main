<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingHistory extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'billing_historiess';

    protected $fillable = [
        "transaction_ref",
        "amount",
        "description",
        "provider",
        "organisation_id"
        // 
        // next_bill_date
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}
