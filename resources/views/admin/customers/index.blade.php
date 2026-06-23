<x-layouts.dashboard title="Customers" header="Customers">
    <form method="GET" class="mb-5 max-w-md">
        <input type="search" name="search" value="{{ $search }}" placeholder="Search businesses…"
               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        @if($businesses->isEmpty())
            <p class="px-5 py-16 text-center text-sm text-slate-400">No customers found.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Business</th>
                            <th class="px-5 py-3">Industry</th>
                            <th class="px-5 py-3">Users</th>
                            <th class="px-5 py-3">Leads</th>
                            <th class="px-5 py-3">Joined</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($businesses as $b)
                            <tr class="cursor-pointer hover:bg-slate-50" onclick="window.location='{{ route('admin.customers.show', $b) }}'">
                                <td class="px-5 py-3">
                                    <p class="font-medium text-slate-900">{{ $b->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $b->email }}</p>
                                </td>
                                <td class="px-5 py-3 capitalize text-slate-600">{{ str_replace('_', ' ', $b->industry ?? 'other') }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $b->users_count }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $b->leads_count }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $b->created_at->format('j M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-4">{{ $businesses->links() }}</div>
</x-layouts.dashboard>
