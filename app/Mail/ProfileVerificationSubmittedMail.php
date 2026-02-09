<?php

namespace App\Mail;

use App\Models\ProfileVerificationSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProfileVerificationSubmittedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public ProfileVerificationSubmission $submission) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Profile Verification Submitted',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.profile-verification-submitted',
            with: [
                'submission' => $this->submission->loadMissing('user:id,name,email'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
