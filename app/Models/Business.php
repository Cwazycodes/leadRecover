<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Laravel\Cashier\Billable;

/**
 * The tenant. Owns all customer data and is the Stripe billable entity.
 *
 * @property string $name
 * @property string $slug
 */
class Business extends Model
{
    /** @use HasFactory<\Database\Factories\BusinessFactory> */
    use Billable, HasFactory;

    protected $fillable = [
        'name', 'slug', 'email', 'phone', 'twilio_number', 'forward_to_number',
        'logo_path', 'industry', 'timezone', 'booking_type', 'booking_url',
        'calendly_url', 'sms_template', 'whatsapp_template', 'whatsapp_enabled',
        'auto_respond_enabled', 'opening_hours', 'settings', 'onboarded_at',
        'trial_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'opening_hours' => 'array',
            'settings' => 'array',
            'whatsapp_enabled' => 'boolean',
            'auto_respond_enabled' => 'boolean',
            'onboarded_at' => 'datetime',
            'trial_ends_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Business $business): void {
            if (empty($business->slug)) {
                $business->slug = static::uniqueSlug($business->name);
            }
        });
    }

    protected static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'business';
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    // ---------------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------------

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class);
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(Interaction::class);
    }

    public function owner(): ?User
    {
        return $this->users()->where('role', 'owner')->first()
            ?? $this->users()->oldest()->first();
    }

    // ---------------------------------------------------------------------
    // Billing / plan helpers
    // ---------------------------------------------------------------------

    /**
     * The Stripe price ID currently subscribed to (if any).
     */
    public function currentPriceId(): ?string
    {
        return $this->subscription('default')?->stripe_price;
    }

    /**
     * Resolve the config plan key (starter/growth/pro) for the active sub.
     */
    public function planKey(): ?string
    {
        $priceId = $this->currentPriceId();

        if (! $priceId) {
            return null;
        }

        foreach (config('leadrecover.plans') as $key => $plan) {
            if ($plan['stripe_price_id'] === $priceId) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function planConfig(): ?array
    {
        $key = $this->planKey();

        return $key ? config("leadrecover.plans.$key") : null;
    }

    public function hasActivePlan(): bool
    {
        return $this->subscribed('default') || $this->onTrial();
    }

    /**
     * Monthly recovered-lead allowance for the current plan (null = unlimited).
     */
    public function leadLimit(): ?int
    {
        return $this->planConfig()['lead_limit'] ?? config('leadrecover.plans.starter.lead_limit');
    }

    public function leadsUsedThisMonth(): int
    {
        return $this->leads()
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
    }

    public function withinLeadLimit(): bool
    {
        $limit = $this->leadLimit();

        return $limit === null || $this->leadsUsedThisMonth() < $limit;
    }

    // ---------------------------------------------------------------------
    // Booking
    // ---------------------------------------------------------------------

    public function bookingLink(): string
    {
        return match ($this->booking_type) {
            'calendly' => $this->calendly_url ?: route('book', $this->slug),
            'url' => $this->booking_url ?: route('book', $this->slug),
            default => route('book', $this->slug),
        };
    }

    public function averageLeadValue(): float
    {
        $industry = config("leadrecover.industries.{$this->industry}")
            ?? config('leadrecover.industries.other');

        return (float) $industry['avg_value'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
