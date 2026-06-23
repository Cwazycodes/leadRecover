<?php

namespace App\Services;

use App\Events\LeadCaptured;
use App\Events\LeadResponded;
use App\Models\Business;
use App\Models\Call;
use App\Models\Interaction;
use App\Models\Lead;
use App\Tenancy\Tenancy;
use Illuminate\Support\Facades\DB;

/**
 * All write-side lead logic lives here so controllers, jobs and webhook
 * handlers share one consistent set of state transitions.
 */
class LeadService
{
    public function __construct(protected Tenancy $tenancy)
    {
    }

    /**
     * Create (or reuse) a lead from an inbound missed call and persist the
     * underlying Call record. Reuses an open lead for the same caller within
     * the last 24h to avoid duplicate opportunities from repeat calls.
     *
     * @param  array{call_sid?: string|null, from: string, to: string, status?: string, occurred_at?: \DateTimeInterface|null}  $callData
     */
    public function createFromMissedCall(Business $business, array $callData): Lead
    {
        [$lead, $isNew] = DB::transaction(function () use ($business, $callData) {
            $lead = $business->leads()
                ->where('phone', $callData['from'])
                ->open()
                ->where('created_at', '>=', now()->subDay())
                ->latest()
                ->first();

            $isNew = $lead === null;

            if ($isNew) {
                $lead = $business->leads()->create([
                    'phone' => $callData['from'],
                    'source' => 'missed_call',
                    'status' => Lead::STATUS_NEW,
                    'last_interaction_at' => now(),
                ]);
            }

            $business->calls()->create([
                'lead_id' => $lead->id,
                'call_sid' => $callData['call_sid'] ?? null,
                'from_number' => $callData['from'],
                'to_number' => $callData['to'],
                'direction' => 'inbound',
                'status' => $callData['status'] ?? 'missed',
                'occurred_at' => $callData['occurred_at'] ?? now(),
            ]);

            $this->logSystem($lead, 'Missed call received from '.$callData['from']);

            return [$lead, $isNew];
        });

        // Dispatch the recovery flow only after the transaction has committed
        // so queue workers never pick up a job for an uncommitted lead.
        if ($isNew) {
            LeadCaptured::dispatch($lead);
        }

        return $lead->refresh();
    }

    /**
     * Manually create a lead from the dashboard.
     *
     * @param  array<string, mixed>  $data
     */
    public function createManual(Business $business, array $data): Lead
    {
        $lead = $business->leads()->create(array_merge([
            'source' => 'manual',
            'status' => Lead::STATUS_NEW,
            'last_interaction_at' => now(),
        ], $data));

        $this->logSystem($lead, 'Lead created manually.');

        return $lead;
    }

    /**
     * Record an outbound message we sent to the lead.
     */
    public function recordOutbound(Lead $lead, string $channel, string $body, array $result = [], ?int $userId = null): Interaction
    {
        $interaction = $lead->interactions()->create([
            'business_id' => $lead->business_id,
            'user_id' => $userId,
            'channel' => $channel,
            'direction' => Interaction::DIRECTION_OUTBOUND,
            'body' => $body,
            'status' => $result['status'] ?? 'sent',
            'provider_sid' => $result['sid'] ?? null,
        ]);

        $lead->forceFill([
            'last_contacted_at' => now(),
            'last_interaction_at' => now(),
            'status' => $lead->status === Lead::STATUS_NEW ? Lead::STATUS_CONTACTED : $lead->status,
        ])->save();

        return $interaction;
    }

    /**
     * Record an inbound reply from the lead and mark them as responded.
     */
    public function recordInbound(Lead $lead, string $channel, string $body, ?string $providerSid = null): Interaction
    {
        $interaction = $lead->interactions()->create([
            'business_id' => $lead->business_id,
            'channel' => $channel,
            'direction' => Interaction::DIRECTION_INBOUND,
            'body' => $body,
            'status' => 'received',
            'provider_sid' => $providerSid,
        ]);

        $alreadyResponded = $lead->hasResponded();

        $lead->forceFill([
            'responded_at' => $lead->responded_at ?? now(),
            'last_interaction_at' => now(),
            'status' => in_array($lead->status, [Lead::STATUS_NEW, Lead::STATUS_STALE], true)
                ? Lead::STATUS_CONTACTED
                : $lead->status,
        ])->save();

        if (! $alreadyResponded) {
            LeadResponded::dispatch($lead, $interaction);
        }

        return $interaction;
    }

    /**
     * Add a private staff note to the lead timeline.
     */
    public function addNote(Lead $lead, string $body, int $userId): Interaction
    {
        return $lead->interactions()->create([
            'business_id' => $lead->business_id,
            'user_id' => $userId,
            'channel' => Interaction::CHANNEL_NOTE,
            'direction' => Interaction::DIRECTION_SYSTEM,
            'body' => $body,
        ]);
    }

    /**
     * Transition a lead's status and keep derived timestamps consistent.
     *
     * @param  array<string, mixed>  $extra
     */
    public function updateStatus(Lead $lead, string $status, array $extra = []): Lead
    {
        $attributes = array_merge(['status' => $status], $extra);

        if ($status === Lead::STATUS_BOOKED && empty($extra['booking_date']) && ! $lead->booking_date) {
            // leave booking_date null; UI prompts for it
        }

        $lead->fill($attributes)->save();

        $this->logSystem($lead, 'Status changed to '.$status.'.');

        return $lead;
    }

    protected function logSystem(Lead $lead, string $message): void
    {
        $lead->interactions()->create([
            'business_id' => $lead->business_id,
            'channel' => Interaction::CHANNEL_SYSTEM,
            'direction' => Interaction::DIRECTION_SYSTEM,
            'body' => $message,
        ]);
    }
}
