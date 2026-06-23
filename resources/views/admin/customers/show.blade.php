<x-layouts.dashboard :title="$business->name" header="Customer">
    <a href="{{ route('admin.customers.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-800">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
        Back to customers
    </a>

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">{{ $business->name }}</h2>
            <p class="text-sm text-slate-500">{{ ucfirst(str_replace('_',' ',$business->industry ?? 'other')) }} · joined {{ $business->created_at->format('j M Y') }}</p>
        </div>
        <div class="text-right text-sm">
            <p class="text-slate-500">Owner</p>
            <p class="font-medium text-slate-900">{{ $owner?->name ?? '—' }}</p>
            <p class="text-slate-500">{{ $owner?->email }}</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat-card label="Leads (90d)" :value="$metrics['leads_total']" tone="brand" />
        <x-stat-card label="Bookings (90d)" :value="$metrics['bookings']" tone="sky" />
        <x-stat-card label="Conversion" :value="$metrics['conversion_rate'].'%'" tone="amber" />
        <x-stat-card label="Revenue recovered" :value="'£'.number_format($metrics['revenue_recovered'])" tone="emerald" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 font-semibold text-slate-900">Account</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Subscription</dt><dd class="font-medium text-slate-900">{{ $business->planConfig()['name'] ?? ($business->onTrial() ? 'Trial' : 'None') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Status</dt><dd class="font-medium text-slate-900">{{ $subscription?->stripe_status ?? ($business->onTrial() ? 'trialing' : 'inactive') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Trial ends</dt><dd class="text-slate-700">{{ $business->trial_ends_at?->format('j M Y') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Twilio number</dt><dd class="text-slate-700">{{ $business->twilio_number ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Total users</dt><dd class="text-slate-700">{{ $business->users_count }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Total calls</dt><dd class="text-slate-700">{{ $business->calls_count }}</dd></div>
            </dl>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 font-semibold text-slate-900">Pipeline (all time)</h3>
            <div class="space-y-3">
                @php $total = max(array_sum($metrics['status_breakdown']), 1); @endphp
                @foreach($metrics['status_breakdown'] as $status => $count)
                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <x-status-badge :status="$status" />
                            <span class="font-semibold text-slate-700">{{ $count }}</span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-brand-500" style="width: {{ round($count / $total * 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.dashboard>
