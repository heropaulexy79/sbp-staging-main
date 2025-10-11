<?php

namespace App\Jobs;

use App\Http\Controllers\SubscriptionController;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessSubscriptionBilling implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $sh = new SubscriptionController();
        $subscriptions = Subscription::where('next_billing_date', '<=', now())
            ->where('status', 'active')
            ->get();

        foreach ($subscriptions as $subscription) {
            $sh->chargeSubscription($subscription);
        }
    }
}
