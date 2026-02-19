<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NightlyAppSyncCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{
     *  started_at:string,
     *  completed_at:string|null,
     *  timezone:string,
     *  fpl_synced:bool,
     *  profile_entries_total:int,
     *  profile_synced:bool,
     *  leagues_total:int,
     *  leagues_synced:int,
     *  league_failures:array<int,array{name:string,error:string}>,
     *  errors:array<int,string>,
     *  duration_seconds:int|null
     * }  $summary
     */
    public function __construct(public array $summary) {}

    public function envelope(): Envelope
    {
        $hasFailures = ($this->summary['errors'] ?? []) !== []
            || ($this->summary['league_failures'] ?? []) !== [];

        return new Envelope(
            subject: $hasFailures
                ? 'Nightly App Sync Finished With Issues'
                : 'Nightly App Sync Completed Successfully',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.nightly-app-sync-completed',
            with: [
                'summary' => $this->summary,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
