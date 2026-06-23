<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\CallFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Call extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<CallFactory> */
    use HasFactory;

    /** Twilio call statuses that we treat as a "missed" opportunity. */
    public const MISSED_STATUSES = ['no-answer', 'busy', 'failed', 'missed', 'voicemail'];

    protected $fillable = [
        'business_id', 'lead_id', 'call_sid', 'from_number', 'to_number',
        'direction', 'status', 'duration', 'recording_url', 'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'duration' => 'integer',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function wasMissed(): bool
    {
        return in_array($this->status, self::MISSED_STATUSES, true);
    }
}
