<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Define the email envelope (subject, from, etc).
     */
    public function envelope(): Envelope
    {
        $app_name = config('app.name');


        return new Envelope(
            subject: "Welcome to {$app_name} 🎉",
        );
    }

    /**
     * Define the email content (view/markdown + data).
     */
    public function content(): Content
    {



        return new Content(
            markdown: 'emails.welcome',
            with: [
                'user' => $this->user,
                'app_name' => config('app.name'),
            ],
        );
    }

    /**
     * Attachments if any.
     */
    public function attachments(): array
    {
        return [];
    }
}
