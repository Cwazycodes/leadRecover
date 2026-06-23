<?php

namespace App\Repositories\Contracts;

use App\Models\Business;
use App\Models\Lead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface LeadRepositoryInterface
{
    /**
     * Paginated, filtered list of a business's leads.
     *
     * @param  array{status?: string|null, search?: string|null, sort?: string|null}  $filters
     * @return LengthAwarePaginator<Lead>
     */
    public function paginateForBusiness(Business $business, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Count of leads per status for the pipeline tabs.
     *
     * @return array<string, int>
     */
    public function statusCounts(Business $business): array;
}
