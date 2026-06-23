<?php

namespace App\Repositories;

use App\Models\Business;
use App\Repositories\Contracts\LeadRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LeadRepository implements LeadRepositoryInterface
{
    public function paginateForBusiness(Business $business, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $sort = $filters['sort'] ?? 'recent';

        return $business->leads()
            ->with(['assignedUser', 'business'])
            ->status($filters['status'] ?? null)
            ->search($filters['search'] ?? null)
            ->when($sort === 'value', fn ($q) => $q->orderByDesc('estimated_value'))
            ->when($sort === 'oldest', fn ($q) => $q->oldest())
            ->when($sort === 'recent', fn ($q) => $q->latest('last_interaction_at')->latest())
            ->paginate($perPage)
            ->withQueryString();
    }

    public function statusCounts(Business $business): array
    {
        $counts = $business->leads()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->toArray();

        $result = ['all' => array_sum($counts)];

        foreach (config('leadrecover.statuses') as $status) {
            $result[$status] = (int) ($counts[$status] ?? 0);
        }

        return $result;
    }
}
