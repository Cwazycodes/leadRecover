<x-layouts.dashboard title="Dashboard" :header="'Welcome back, '.explode(' ', auth()->user()->name)[0]">

    @if($needsPlan)
        <div class="mb-6 flex flex-col items-start justify-between gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-5 sm:flex-row sm:items-center">
            <div>
                <p class="font-semibold text-amber-900">
                    @if($business->onTrial())
                        You're on a free trial — {{ $business->trial_ends_at?->diffForHumans() }}.
                    @else
                        Your trial has ended.
                    @endif
                </p>
                <p class="text-sm text-amber-700">Choose a plan to keep recovering missed calls without interruption.</p>
            </div>
            <a href="{{ route('billing.index') }}" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">Choose a plan</a>
        </div>
    @endif

    {{-- Range switch --}}
    <div class="mb-5 flex items-center justify-between">
        <p class="text-sm text-slate-500">Performance over the last {{ $range }} days</p>
        <div class="inline-flex rounded-lg border border-slate-200 bg-white p-0.5 text-sm">
            @foreach([7 => '7d', 30 => '30d', 90 => '90d'] as $value => $label)
                <a href="{{ route('dashboard', ['range' => $value]) }}"
                   class="rounded-md px-3 py-1 font-medium {{ $range === $value ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
        <x-stat-card label="Calls missed" :value="$metrics['calls_missed']" tone="rose">
            <x-slot:icon><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 0 1 2-2h2l1 5-2 1a11 11 0 0 0 5 5l1-2 5 1v2a2 2 0 0 1-2 2A16 16 0 0 1 3 5Z"/></svg></x-slot:icon>
        </x-stat-card>
        <x-stat-card label="Leads recovered" :value="$metrics['leads_recovered']" tone="brand">
            <x-slot:icon><x-icon.users class="h-5 w-5" /></x-slot:icon>
        </x-stat-card>
        <x-stat-card label="Bookings" :value="$metrics['bookings']" tone="sky">
            <x-slot:icon><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 2v4M8 2v4M3 10h18"/></svg></x-slot:icon>
        </x-stat-card>
        <x-stat-card label="Conversion rate" :value="$metrics['conversion_rate'].'%'" tone="amber">
            <x-slot:icon><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m3 17 6-6 4 4 8-8M21 7v6h-6"/></svg></x-slot:icon>
        </x-stat-card>
        <x-stat-card label="Revenue recovered" :value="'£'.number_format($metrics['revenue_recovered'])" sub="estimated" tone="emerald">
            <x-slot:icon><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></x-slot:icon>
        </x-stat-card>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        {{-- Chart --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="font-semibold text-slate-900">Leads recovered</h2>
            </div>
            <canvas id="leadsChart" height="110"></canvas>
        </div>

        {{-- Pipeline --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 font-semibold text-slate-900">Pipeline</h2>
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

    {{-- Recent leads --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h2 class="font-semibold text-slate-900">Recent leads</h2>
            <a href="{{ route('leads.index') }}" class="text-sm font-medium text-brand-600 hover:underline">View all</a>
        </div>
        @if($recentLeads->isEmpty())
            <p class="px-5 py-12 text-center text-sm text-slate-400">No leads yet. As soon as a call is missed, it'll appear here.</p>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($recentLeads as $lead)
                    <a href="{{ route('leads.show', $lead) }}" class="flex items-center justify-between px-5 py-3 hover:bg-slate-50">
                        <div class="min-w-0">
                            <p class="truncate font-medium text-slate-900">{{ $lead->displayName() }}</p>
                            <p class="text-xs text-slate-500">{{ $lead->phone }} · {{ str_replace('_', ' ', $lead->source) }} · {{ $lead->created_at->diffForHumans() }}</p>
                        </div>
                        <x-status-badge :status="$lead->status" />
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            const ctx = document.getElementById('leadsChart');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($metrics['series']['labels']),
                    datasets: [{
                        label: 'Leads',
                        data: @json($metrics['series']['data']),
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79,70,229,0.08)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 0,
                        borderWidth: 2,
                    }]
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                        x: { grid: { display: false }, ticks: { maxTicksLimit: 8 } }
                    }
                }
            });
        </script>
    @endpush
</x-layouts.dashboard>
