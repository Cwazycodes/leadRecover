<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLeadNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Lead $lead) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🔔 New lead recovered: '.$this->lead->displayName())
            ->greeting('Good news, '.$notifiable->name.'!')
            ->line('A new lead just came in from a '.str_replace('_', ' ', $this->lead->source).'.')
            ->line('**Phone:** '.$this->lead->phone)
            ->line('We\'ve automatically sent them a message to win the booking back.')
            ->action('View lead', route('leads.show', $this->lead))
            ->line('Reply fast — speed is the #1 driver of recovered revenue.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'lead_id' => $this->lead->id,
            'title' => 'New lead recovered',
            'message' => $this->lead->displayName().' just contacted you.',
            'phone' => $this->lead->phone,
            'url' => route('leads.show', $this->lead),
        ];
    }
}
