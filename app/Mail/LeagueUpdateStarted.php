<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeagueUpdateStarted extends Mailable
{
    use Queueable, SerializesModels;

 

public function build()
{
    return $this->subject('League Update Started')
        ->markdown('emails.league-update-started');
}


}
