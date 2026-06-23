<x-layouts.dashboard title="Platform overview" header="Platform overview">
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat-card label="Customers" :value="number_format($metrics['total_customers'])" tone="brand">
            <x-slot:icon><x-icon.building class="h-5 w-5" /></x-slot:icon>
        </x-stat-card>
        <x-stat-card label="MRR" :value="'£'.number_format($metrics['mrr'])" :sub="'£'.number_format($metrics['arr']).' ARR'" tone="emerald">
            <x-slot:icon><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></x-slot:icon>
        </x-stat-card>
        <x-stat-card label="Active subscriptions" :value="number_format($metrics['active_subscriptions'])" tone="sky">
            <x-slot:icon><x-icon.card class="h-5 w-5" /></x-slot:icon>
        </x-stat-card>
        <x-stat-card label="Churn rate" :value="$metrics['churn_rate'].'%'" tone="rose">
            <x-slot:icon><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m3 7 6 6 4-4 8 8M21 17v-6"/></svg></x-slot:icon>
        </x-stat-card>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
            <h2 class="mb-4 font-semibold text-slate-900">Monthly recurring revenue</h2>
            <canvas id="mrrChart" height="110"></canvas>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 font-semibold text-slate-900">New customers</h2>
            <canvas id="signupsChart" height="160"></canvas>
        </div>
    </div>

    <div class="mt-6 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h2 class="font-semibold text-slate-900">Recent signups</h2>
            <a href="{{ route('admin.customers.index') }}" class="text-sm font-medium text-brand-600 hover:underline">All customers</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($recentBusinesses as $b)
                <a href="{{ route('admin.customers.show', $b) }}" class="flex items-center justify-between px-5 py-3 hover:bg-slate-50">
                    <div>
                        <p class="font-medium text-slate-900">{{ $b->name }}</p>
                        <p class="text-xs text-slate-500">{{ ucfirst($b->industry ?? 'other') }} · joined {{ $b->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="text-sm text-slate-500">{{ $b->leads_count }} leads</span>
                </a>
            @empty
                <p class="px-5 py-12 text-center text-sm text-slate-400">No customers yet.</p>
            @endforelse
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            new Chart(document.getElementById('mrrChart'), {
                type: 'line',
                data: {
                    labels: @json($metrics['revenue_series']['labels']),
                    datasets: [{ data: @json($metrics['revenue_series']['data']), borderColor: '#059669', backgroundColor: 'rgba(5,150,105,0.08)', fill: true, tension: 0.35, pointRadius: 0, borderWidth: 2 }]
                },
                options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
            });
            new Chart(document.getElementById('signupsChart'), {
                type: 'bar',
                data: {
                    labels: @json($metrics['signups_series']['labels']),
                    datasets: [{ data: @json($metrics['signups_series']['data']), backgroundColor: '#6366f1', borderRadius: 6 }]
                },
                options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
            });
        </script>
    @endpush
</x-layouts.dashboard>
