<?php

namespace App\Mail;

use App\Models\Business;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Business $business, public User $user)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Welcome to LeadRecover 🎉');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.welcome', with: [
            'business' => $this->business,
            'user' => $this->user,
            'trialEndsAt' => $this->business->trial_ends_at,
        ]);
    }
}
