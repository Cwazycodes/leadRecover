<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Services\AnalyticsService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __construct(protected AnalyticsService $analytics) {}

    public function index(): View
    {
        return view('admin.dashboard', [
            'metrics' => $this->analytics->forPlatform(),
            'recentBusinesses' => Business::withCount('leads')->latest()->limit(8)->get(),
        ]);
    }
}
