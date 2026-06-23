<?php

namespace App\Mail;

use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialEndingEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Business $business) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your LeadRecover trial ends soon');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.trial-ending', with: [
            'business' => $this->business,
            'trialEndsAt' => $this->business->trial_ends_at,
        ]);
    }
}
