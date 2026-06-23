<?php

namespace App\Http\Controllers;

use App\Tenancy\Tenancy;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function __construct(protected Tenancy $tenancy) {}

    public function edit(): View
    {
        $business = $this->tenancy->current();
        $this->authorize('manage', $business);

        return view('settings.edit', [
            'business' => $business,
            'industries' => config('leadrecover.industries'),
            'timezones' => $this->timezones(),
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $business = $this->tenancy->current();
        $this->authorize('manage', $business);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:32'],
            'twilio_number' => ['nullable', 'string', 'max:32'],
            'forward_to_number' => ['nullable', 'string', 'max:32'],
            'industry' => ['nullable', Rule::in(array_keys(config('leadrecover.industries')))],
            'timezone' => ['required', 'timezone'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            if ($business->logo_path) {
                Storage::disk('public')->delete($business->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        unset($data['logo']);

        $business->update($data);

        return back()->with('success', 'Business profile updated.');
    }

    public function updateTemplates(Request $request): RedirectResponse
    {
        $business = $this->tenancy->current();
        $this->authorize('manage', $business);

        $data = $request->validate([
            'sms_template' => ['nullable', 'string', 'max:480'],
            'whatsapp_template' => ['nullable', 'string', 'max:1000'],
            'whatsapp_enabled' => ['nullable', 'boolean'],
            'auto_respond_enabled' => ['nullable', 'boolean'],
        ]);

        $business->update([
            'sms_template' => $data['sms_template'] ?? null,
            'whatsapp_template' => $data['whatsapp_template'] ?? null,
            'whatsapp_enabled' => $request->boolean('whatsapp_enabled'),
            'auto_respond_enabled' => $request->boolean('auto_respond_enabled'),
        ]);

        return back()->with('success', 'Message templates saved.');
    }

    public function updateBooking(Request $request): RedirectResponse
    {
        $business = $this->tenancy->current();
        $this->authorize('manage', $business);

        $data = $request->validate([
            'booking_type' => ['required', Rule::in(['internal', 'url', 'calendly'])],
            'booking_url' => ['nullable', 'url', 'max:255', 'required_if:booking_type,url'],
            'calendly_url' => ['nullable', 'url', 'max:255', 'required_if:booking_type,calendly'],
        ]);

        $business->update($data);

        return back()->with('success', 'Booking settings saved.');
    }

    public function updateHours(Request $request): RedirectResponse
    {
        $business = $this->tenancy->current();
        $this->authorize('manage', $business);

        $validated = $request->validate([
            'hours' => ['array'],
            'hours.*.open' => ['nullable', 'date_format:H:i'],
            'hours.*.close' => ['nullable', 'date_format:H:i'],
            'hours.*.closed' => ['nullable', 'boolean'],
        ]);

        $business->update(['opening_hours' => $validated['hours'] ?? []]);

        return back()->with('success', 'Opening hours saved.');
    }

    /**
     * @return array<int, string>
     */
    protected function timezones(): array
    {
        return array_merge(
            \DateTimeZone::listIdentifiers(\DateTimeZone::EUROPE),
            \DateTimeZone::listIdentifiers(\DateTimeZone::AMERICA),
        );
    }
}
