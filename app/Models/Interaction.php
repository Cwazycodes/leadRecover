<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interaction extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<\Database\Factories\InteractionFactory> */
    use HasFactory;

    public const CHANNEL_SMS = 'sms';
    public const CHANNEL_WHATSAPP = 'whatsapp';
    public const CHANNEL_VOICE = 'voice';
    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_NOTE = 'note';
    public const CHANNEL_SYSTEM = 'system';

    public const DIRECTION_INBOUND = 'inbound';
    public const DIRECTION_OUTBOUND = 'outbound';
    public const DIRECTION_SYSTEM = 'system';

    protected $fillable = [
        'business_id', 'lead_id', 'user_id', 'channel', 'direction',
        'body', 'status', 'provider_sid', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isInbound(): bool
    {
        return $this->direction === self::DIRECTION_INBOUND;
    }
}
