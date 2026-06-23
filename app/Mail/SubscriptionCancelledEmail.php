<?php

namespace App\Mail;

use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionCancelledEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Business $business)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your LeadRecover subscription has been cancelled');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.subscription-cancelled', with: [
            'business' => $this->business,
            'endsAt' => $this->business->subscription('default')?->ends_at,
        ]);
    }
}
