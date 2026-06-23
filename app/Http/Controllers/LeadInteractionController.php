<?php

namespace App\Http\Controllers;

use App\Models\Interaction;
use App\Models\Lead;
use App\Services\LeadService;
use App\Services\TwilioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class LeadInteractionController extends Controller
{
    public function __construct(protected TwilioService $twilio, protected LeadService $leads) {}

    public function store(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $data = $request->validate([
            'channel' => ['required', Rule::in(['sms', 'whatsapp', 'note'])],
            'body' => ['required', 'string', 'max:1000'],
        ]);

        if ($data['channel'] === Interaction::CHANNEL_NOTE) {
            $this->leads->addNote($lead, $data['body'], $request->user()->id);

            return back()->with('success', 'Note added.');
        }

        try {
            $result = $data['channel'] === 'whatsapp'
                ? $this->twilio->sendWhatsApp($lead->phone, $data['body'])
                : $this->twilio->sendSms($lead->phone, $data['body']);

            $this->leads->recordOutbound($lead, $data['channel'], $data['body'], $result, $request->user()->id);
        } catch (\Throwable $e) {
            Log::error('Manual message send failed', ['lead_id' => $lead->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Message could not be sent. Please try again.');
        }

        return back()->with('success', 'Message sent.');
    }
}
