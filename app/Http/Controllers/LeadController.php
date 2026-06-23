<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Repositories\Contracts\LeadRepositoryInterface;
use App\Services\LeadService;
use App\Tenancy\Tenancy;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function __construct(
        protected Tenancy $tenancy,
        protected LeadService $leads,
        protected LeadRepositoryInterface $repository,
    ) {}

    public function index(Request $request): View
    {
        $business = $this->tenancy->current();

        $filters = [
            'status' => $request->string('status')->toString() ?: null,
            'search' => $request->string('search')->toString() ?: null,
            'sort' => $request->string('sort')->toString() ?: 'recent',
        ];

        return view('leads.index', [
            'leads' => $this->repository->paginateForBusiness($business, $filters),
            'counts' => $this->repository->statusCounts($business),
            'filters' => $filters,
            'statuses' => config('leadrecover.statuses'),
        ]);
    }

    public function show(Lead $lead): View
    {
        $this->authorize('view', $lead);

        $lead->load(['interactions.user', 'assignedUser', 'calls']);

        return view('leads.show', [
            'lead' => $lead,
            'statuses' => config('leadrecover.statuses'),
            'teamMembers' => $this->tenancy->current()->users()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $business = $this->tenancy->current();

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:160'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $lead = $this->leads->createManual($business, $data);

        return redirect()
            ->route('leads.show', $lead)
            ->with('success', 'Lead created.');
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:160'],
            'status' => ['nullable', Rule::in(config('leadrecover.statuses'))],
            'notes' => ['nullable', 'string', 'max:2000'],
            'booking_date' => ['nullable', 'date'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'assigned_user_id' => [
                'nullable',
                Rule::exists('users', 'id')->where('business_id', $lead->business_id),
            ],
        ]);

        $status = $data['status'] ?? $lead->status;
        unset($data['status']);

        // Persist the editable fields first…
        $lead->fill($data)->save();

        // …then route the status change through the service so timeline +
        // derived timestamps stay consistent.
        if ($status !== $lead->status) {
            $this->leads->updateStatus($lead, $status);
        }

        return redirect()
            ->route('leads.show', $lead)
            ->with('success', 'Lead updated.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $this->authorize('delete', $lead);

        $lead->delete();

        return redirect()
            ->route('leads.index')
            ->with('success', 'Lead deleted.');
    }
}
