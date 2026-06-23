<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use App\Tenancy\Tenancy;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected Tenancy $tenancy, protected AnalyticsService $analytics)
    {
    }

    public function index(Request $request): View
    {
        $business = $this->tenancy->current();
        $days = (int) $request->integer('range', 30);
        $days = in_array($days, [7, 30, 90], true) ? $days : 30;

        $metrics = $this->analytics->forBusiness($business, $days);

        $recentLeads = $business->leads()
            ->latest()
            ->limit(8)
            ->get();

        return view('dashboard', [
            'business' => $business,
            'metrics' => $metrics,
            'recentLeads' => $recentLeads,
            'range' => $days,
            'needsPlan' => ! $business->hasActivePlan(),
        ]);
    }
}
