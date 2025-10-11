<?php

namespace App\Listeners;

use App\Events\EmployeeInvited;
use App\Notifications\NewEmployeeInvitation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendEmployeeInvitationNotifications
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
    public function handle(EmployeeInvited $event): void
    {
        $invitation = $event->invitation;
        $notification = new NewEmployeeInvitation($invitation);
        Notification::route('mail', $invitation->email)->notify($notification);
    }
}
