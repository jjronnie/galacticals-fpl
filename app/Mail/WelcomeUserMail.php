<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeUserMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public string $appName;
    public string $youtubeLink = 'https://www.youtube.com/embed/rC8nyLdnBf0?si=J93XJrNL_OupYsDn'; // <-- REPLACE with your actual video ID/link

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->appName = config('app.name');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to ' . $this->appName . '!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.welcome-user',
            // Pass data to the template
            with: [
                'name' => $this->user->name,
                'appName' => $this->appName,
                'youtubeLink' => $this->youtubeLink,
                'findRoute' => route('find'), // <-- Make sure you have a route named 'find'
            ],
        );
    }
    // ... rest of the file
}