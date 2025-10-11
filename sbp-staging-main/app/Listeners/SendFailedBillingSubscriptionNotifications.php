<?php

namespace App\Listeners;

use App\Events\SubscriptionBillingFailed;
use App\Notifications\FailedSubscriptionBilling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendFailedBillingSubscriptionNotifications implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SubscriptionBillingFailed $event): void
    {
        //
        $organisation = $event->organisation;
        $notification = new FailedSubscriptionBilling($organisation);

        $pm = $organisation->paymentMethods->first();

        Notification::route('mail', $pm->email_address)->notify($notification);
    }
}
