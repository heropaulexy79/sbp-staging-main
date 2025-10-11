<?php

namespace App\Notifications;

use App\Models\Organisation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FailedSubscriptionBilling extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Organisation $organisation)
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
        $org_name = $this->organisation;
        $app_name = config('app.name');





        return (new MailMessage)
            ->subject("Update payment method on {$org_name}")
            ->greeting('Hello!')
            ->line('We hope you\'re enjoying our services. Unfortunately, we were unable to process your recent payment due to an issue with your billing information.')
            ->line('To avoid any interruption, please update your payment method as soon as possible.')
            ->action('Update payment method', url("/settings/billing"))
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
