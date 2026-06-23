<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Call;
use App\Models\Lead;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laravel\Cashier\Subscription;

class AnalyticsService
{
    /**
     * Headline metrics + chart series for a single business dashboard.
     *
     * @return array<string, mixed>
     */
    public function forBusiness(Business $business, int $days = 30): array
    {
        $since = now()->subDays($days)->startOfDay();

        $leads = $business->leads()->where('created_at', '>=', $since);
        $totalLeads = (clone $leads)->count();

        $callsMissed = $business->calls()
            ->whereIn('status', Call::MISSED_STATUSES)
            ->where('occurred_at', '>=', $since)
            ->count();

        $recovered = (clone $leads)
            ->whereIn('status', [Lead::STATUS_CONTACTED, Lead::STATUS_BOOKED, Lead::STATUS_CONVERTED])
            ->count();

        $bookings = (clone $leads)
            ->whereIn('status', [Lead::STATUS_BOOKED, Lead::STATUS_CONVERTED])
            ->count();

        $conversionRate = $totalLeads > 0 ? round($bookings / $totalLeads * 100, 1) : 0.0;

        $revenueRecovered = (clone $leads)
            ->whereIn('status', [Lead::STATUS_BOOKED, Lead::STATUS_CONVERTED])
            ->get()
            ->sum(fn (Lead $lead) => $lead->estimatedValue());

        return [
            'range_days' => $days,
            'calls_missed' => $callsMissed,
            'leads_total' => $totalLeads,
            'leads_recovered' => $recovered,
            'bookings' => $bookings,
            'conversion_rate' => $conversionRate,
            'revenue_recovered' => round($revenueRecovered, 2),
            'status_breakdown' => $this->statusBreakdown($business),
            'series' => $this->leadsSeries($business, $days),
        ];
    }

    /**
     * Count of currently-open leads grouped by status (for pipeline cards).
     *
     * @return array<string, int>
     */
    public function statusBreakdown(Business $business): array
    {
        $counts = $business->leads()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->toArray();

        $result = [];
        foreach (config('leadrecover.statuses') as $status) {
            $result[$status] = (int) ($counts[$status] ?? 0);
        }

        return $result;
    }

    /**
     * Daily new-lead counts for the dashboard chart.
     *
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    public function leadsSeries(Business $business, int $days = 30): array
    {
        $rows = $business->leads()
            ->where('created_at', '>=', now()->subDays($days)->startOfDay())
            ->get(['created_at'])
            ->groupBy(fn (Lead $lead) => $lead->created_at->toDateString())
            ->map->count();

        return $this->fillSeries($rows, $days);
    }

    // ---------------------------------------------------------------------
    // Platform / admin analytics
    // ---------------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    public function forPlatform(): array
    {
        $totalCustomers = Business::count();

        $activeSubscriptions = Subscription::query()
            ->whereIn('stripe_status', ['active', 'trialing'])
            ->whereNull('ends_at')
            ->count();

        $mrr = $this->monthlyRecurringRevenue();

        // Simple churn proxy: subscriptions that ended in the last 30 days vs
        // the active base at the start of that window.
        $cancelledLast30 = Subscription::where('ends_at', '>=', now()->subDays(30))->count();
        $churnBase = max($activeSubscriptions + $cancelledLast30, 1);
        $churnRate = round($cancelledLast30 / $churnBase * 100, 1);

        return [
            'total_customers' => $totalCustomers,
            'active_subscriptions' => $activeSubscriptions,
            'mrr' => $mrr,
            'arr' => $mrr * 12,
            'churn_rate' => $churnRate,
            'revenue_series' => $this->revenueSeries(),
            'signups_series' => $this->signupsSeries(),
        ];
    }

    public function monthlyRecurringRevenue(): float
    {
        $priceToAmount = collect(config('leadrecover.plans'))
            ->mapWithKeys(fn ($plan) => [$plan['stripe_price_id'] => $plan['price']]);

        return (float) Subscription::query()
            ->where('stripe_status', 'active')
            ->whereNull('ends_at')
            ->get()
            ->sum(fn (Subscription $sub) => $priceToAmount[$sub->stripe_price] ?? 0);
    }

    /**
     * Estimated MRR per month over the last 6 months based on signup dates.
     *
     * @return array{labels: array<int, string>, data: array<int, float>}
     */
    public function revenueSeries(int $months = 6): array
    {
        $priceToAmount = collect(config('leadrecover.plans'))
            ->mapWithKeys(fn ($plan) => [$plan['stripe_price_id'] => $plan['price']]);

        $labels = [];
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $monthEnd = now()->subMonths($i)->endOfMonth();
            $labels[] = $monthEnd->format('M Y');

            $mrr = Subscription::where('created_at', '<=', $monthEnd)
                ->where('stripe_status', 'active')
                ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', $monthEnd))
                ->get()
                ->sum(fn (Subscription $sub) => $priceToAmount[$sub->stripe_price] ?? 0);

            $data[] = (float) $mrr;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    public function signupsSeries(int $months = 6): array
    {
        $labels = [];
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->format('M Y');
            $data[] = Business::whereBetween('created_at', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
            ])->count();
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Normalise a sparse date=>count collection into continuous daily series.
     *
     * @param  Collection<string, int>  $rows
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    protected function fillSeries(Collection $rows, int $days): array
    {
        $labels = [];
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('d M');
            $data[] = (int) ($rows[$date->toDateString()] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
