<?php

namespace App\Notifications;

use App\Models\Interaction;
use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LeadRepliedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Lead $lead, public Interaction $interaction)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'lead_id' => $this->lead->id,
            'title' => 'Lead replied',
            'message' => $this->lead->displayName().' replied: "'.\Illuminate\Support\Str::limit($this->interaction->body, 60).'"',
            'url' => route('leads.show', $this->lead),
        ];
    }
}
