<x-layouts.dashboard title="Leads" header="Leads">
    <div x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }} }">
        {{-- Toolbar --}}
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <form method="GET" class="flex w-full max-w-md gap-2">
                <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Search name, phone or email…"
                       class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                @if($filters['status'])<input type="hidden" name="status" value="{{ $filters['status'] }}">@endif
                <select name="sort" onchange="this.form.submit()" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="recent" @selected($filters['sort']==='recent')>Most recent</option>
                    <option value="oldest" @selected($filters['sort']==='oldest')>Oldest</option>
                    <option value="value" @selected($filters['sort']==='value')>Highest value</option>
                </select>
            </form>
            <button @click="createOpen = true" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                Add lead
            </button>
        </div>

        {{-- Status tabs --}}
        <div class="mb-5 flex flex-wrap gap-2">
            <a href="{{ route('leads.index', array_filter(['search' => $filters['search'], 'sort' => $filters['sort']])) }}"
               class="rounded-full px-3 py-1.5 text-sm font-medium {{ ! $filters['status'] ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' }}">
                All <span class="ml-1 opacity-70">{{ $counts['all'] ?? 0 }}</span>
            </a>
            @foreach($statuses as $status)
                <a href="{{ route('leads.index', array_filter(['status' => $status, 'search' => $filters['search'], 'sort' => $filters['sort']])) }}"
                   class="rounded-full px-3 py-1.5 text-sm font-medium capitalize {{ $filters['status'] === $status ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' }}">
                    {{ $status }} <span class="ml-1 opacity-70">{{ $counts[$status] ?? 0 }}</span>
                </a>
            @endforeach
        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @if($leads->isEmpty())
                <p class="px-5 py-16 text-center text-sm text-slate-400">No leads found. Missed calls will appear here automatically.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3">Lead</th>
                                <th class="px-5 py-3">Source</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Est. value</th>
                                <th class="px-5 py-3">Last activity</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($leads as $lead)
                                <tr class="cursor-pointer hover:bg-slate-50" onclick="window.location='{{ route('leads.show', $lead) }}'">
                                    <td class="px-5 py-3">
                                        <p class="font-medium text-slate-900">{{ $lead->displayName() }}</p>
                                        <p class="text-xs text-slate-500">{{ $lead->phone }}</p>
                                    </td>
                                    <td class="px-5 py-3 capitalize text-slate-600">{{ str_replace('_', ' ', $lead->source) }}</td>
                                    <td class="px-5 py-3"><x-status-badge :status="$lead->status" /></td>
                                    <td class="px-5 py-3 text-slate-600">£{{ number_format($lead->estimatedValue()) }}</td>
                                    <td class="px-5 py-3 text-slate-500">{{ ($lead->last_interaction_at ?? $lead->created_at)->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="mt-4">{{ $leads->links() }}</div>

        {{-- Create lead modal --}}
        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div @click="createOpen = false" class="absolute inset-0 bg-slate-900/40"></div>
            <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl" x-transition>
                <h3 class="text-lg font-semibold text-slate-900">Add a lead</h3>
                <form method="POST" action="{{ route('leads.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Name</label>
                        <input name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Phone <span class="text-rose-500">*</span></label>
                        <input name="phone" value="{{ old('phone') }}" required placeholder="+447700900123" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Email</label>
                        <input name="email" type="email" value="{{ old('email') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Estimated value (£)</label>
                        <input name="estimated_value" type="number" step="0.01" min="0" value="{{ old('estimated_value') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="createOpen = false" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                        <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Create lead</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
