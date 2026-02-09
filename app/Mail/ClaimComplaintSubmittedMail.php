<?php

namespace App\Mail;

use App\Models\ClaimsComplaint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClaimComplaintSubmittedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public ClaimsComplaint $complaint) {}

    public function build(): self
    {
        return $this
            ->subject('New Profile Claim Complaint Submitted')
            ->view('emails.claim-complaint-submitted');
    }
}
