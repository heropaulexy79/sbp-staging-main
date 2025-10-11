<?php

namespace App\Notifications;

use App\Models\OrganisationInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewEmployeeInvitation extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public OrganisationInvitation $invitation)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $org_name = $this->invitation->organisation->name;
        $token = $this->invitation->token;

        $app_name = config('app.name');

        return (new MailMessage)
            ->subject("Invitation from {$org_name}")
            ->greeting('Hello!')
            ->line("You have been invited to join {$org_name} on {$app_name} ")
            ->action('Create your account', url("/signup/?tk={$token}"))
            ->line("Thank you for using {$app_name}!");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
