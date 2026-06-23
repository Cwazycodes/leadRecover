<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Lead;
use App\Notifications\NewLeadNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

/**
 * Public, unauthenticated booking page used when a business chooses the
 * built-in booking link. External booking types redirect straight out.
 */
class BookingController extends Controller
{
    public function show(Business $business): View|RedirectResponse
    {
        if (in_array($business->booking_type, ['url', 'calendly'], true)) {
            $url = $business->booking_type === 'calendly' ? $business->calendly_url : $business->booking_url;

            if ($url) {
                return redirect()->away($url);
            }
        }

        return view('booking.show', ['business' => $business]);
    }

    public function store(Request $request, Business $business): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:160'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $lead = $business->leads()->create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'booking_date' => $data['booking_date'],
            'notes' => $data['notes'] ?? null,
            'source' => 'web_form',
            'status' => Lead::STATUS_BOOKED,
            'last_interaction_at' => now(),
        ]);

        $lead->interactions()->create([
            'business_id' => $business->id,
            'channel' => 'system',
            'direction' => 'system',
            'body' => 'Appointment requested via booking page for '.$lead->booking_date->format('jS M Y H:i'),
        ]);

        Notification::send($business->users, new NewLeadNotification($lead));

        return redirect()
            ->route('book', $business)
            ->with('booked', true);
    }
}
