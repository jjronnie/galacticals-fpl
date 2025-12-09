<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ImportLeagueReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $appName;

    public function __construct($user)
    {
        $this->user = $user;
        $this->appName = config('app.name');
    }

    public function build()
    {
        return $this->subject('Reminder to Complete Your League Setup')
            ->markdown('emails.import-league-reminder');
    }
}
