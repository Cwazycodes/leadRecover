<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Lead;

/**
 * Renders a business's message templates, substituting the supported tokens:
 *   {{name}}         – the lead's name (or a friendly fallback)
 *   {{business}}     – the business name
 *   {{booking_link}} – the resolved booking URL
 */
class MessageComposer
{
    public function render(string $template, Business $business, Lead $lead): string
    {
        return strtr($template, [
            '{{name}}' => $lead->name ?: 'there',
            '{{business}}' => $business->name,
            '{{booking_link}}' => $business->bookingLink(),
        ]);
    }

    /**
     * Resolve the body for a given stage of the recovery flow, falling back to
     * the platform default template when the business hasn't customised one.
     */
    public function forStage(string $stage, Business $business, Lead $lead, string $channel): string
    {
        $template = match ($stage) {
            'initial' => $channel === 'whatsapp'
                ? ($business->whatsapp_template ?: config('leadrecover.templates.whatsapp'))
                : ($business->sms_template ?: config('leadrecover.templates.sms')),
            'first_reminder' => config('leadrecover.templates.first_reminder'),
            'second_reminder' => config('leadrecover.templates.second_reminder'),
            default => config('leadrecover.templates.sms'),
        };

        return $this->render($template, $business, $lead);
    }
}
