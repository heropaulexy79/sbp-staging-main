<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function employees()
    {
        return $this->hasMany(OrganisationUser::class, 'organisation_id');
    }

    public function invites()
    {
        return $this->hasMany(OrganisationInvitation::class, 'organisation_id');
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class, 'organisation_id');
    }

    public function billingHistories()
    {
        return $this->hasMany(BillingHistory::class, 'organisation_id');
    }


    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'organisation_id');
    }


    public function activeSubscription()
    {
        return $this->subscriptions()
            ->where(function ($query) {
                $query->where('status', 'active')
                    ->where('next_billing_date', '>', now()->addHours(23));
            })
            ->orWhere(function ($query) {
                $query->where('status', 'cancelled')
                    ->where('next_billing_date', '>', now());
            })
            ->first();
    }


    public function courses()
    {
        return $this->hasMany(Course::class, 'organisation_id');
    }

    public function groups()
    {
        return $this->hasMany(Group::class, 'organisation_id');
    }
}
