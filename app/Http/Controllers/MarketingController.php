<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class MarketingController extends Controller
{
    public function home(): View
    {
        return view('marketing.home', [
            'plans' => config('leadrecover.plans'),
            'industries' => config('leadrecover.industries'),
        ]);
    }

    public function pricing(): View
    {
        return view('marketing.pricing', [
            'plans' => config('leadrecover.plans'),
            'trialDays' => config('leadrecover.trial_days'),
        ]);
    }
}
