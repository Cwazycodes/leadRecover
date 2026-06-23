<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Services\AnalyticsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(protected AnalyticsService $analytics) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $businesses = Business::query()
            ->withCount(['leads', 'users'])
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', [
            'businesses' => $businesses,
            'search' => $search,
        ]);
    }

    public function show(Business $business): View
    {
        return view('admin.customers.show', [
            'business' => $business->loadCount(['leads', 'users', 'calls']),
            'metrics' => $this->analytics->forBusiness($business, 90),
            'subscription' => $business->subscription('default'),
            'owner' => $business->owner(),
        ]);
    }
}
