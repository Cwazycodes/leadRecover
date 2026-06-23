<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    public const STATUS_NEW = 'new';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_BOOKED = 'booked';

    public const STATUS_CONVERTED = 'converted';

    public const STATUS_LOST = 'lost';

    public const STATUS_STALE = 'stale';

    /** Statuses that mean the recovery flow should stop chasing the lead. */
    public const CLOSED_STATUSES = [
        self::STATUS_BOOKED,
        self::STATUS_CONVERTED,
        self::STATUS_LOST,
    ];

    protected $fillable = [
        'business_id', 'assigned_user_id', 'name', 'phone', 'email', 'source',
        'status', 'notes', 'booking_date', 'estimated_value', 'follow_up_stage',
        'last_contacted_at', 'last_interaction_at', 'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'datetime',
            'last_contacted_at' => 'datetime',
            'last_interaction_at' => 'datetime',
            'responded_at' => 'datetime',
            'estimated_value' => 'decimal:2',
            'follow_up_stage' => 'integer',
        ];
    }

    // ---------------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------------

    public function interactions(): HasMany
    {
        return $this->hasMany(Interaction::class)->latest();
    }

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    // ---------------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------------

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', self::CLOSED_STATUSES);
    }

    // ---------------------------------------------------------------------
    // State helpers
    // ---------------------------------------------------------------------

    public function hasResponded(): bool
    {
        return $this->responded_at !== null;
    }

    public function isClosed(): bool
    {
        return in_array($this->status, self::CLOSED_STATUSES, true);
    }

    /**
     * Whether automated follow-ups should still be sent.
     */
    public function shouldFollowUp(): bool
    {
        return ! $this->isClosed() && ! $this->hasResponded();
    }

    public function displayName(): string
    {
        return $this->name ?: $this->phone;
    }

    /**
     * Estimated recoverable value: explicit value, else the business average.
     */
    public function estimatedValue(): float
    {
        if ($this->estimated_value !== null) {
            return (float) $this->estimated_value;
        }

        return $this->business?->averageLeadValue() ?? 0.0;
    }
}
