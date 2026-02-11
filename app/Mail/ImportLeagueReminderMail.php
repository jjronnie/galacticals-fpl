<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ImportLeagueReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public mixed $user;

    public string $appName;

    public function __construct(mixed $user)
    {
        $this->user = $user;
        $this->appName = config('app.name');
    }

    public function build(): static
    {
        return $this->subject('Complete Your FPL Setup')
            ->markdown('emails.import-league-reminder');
    }
}
