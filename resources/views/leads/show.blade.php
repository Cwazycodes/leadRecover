<x-layouts.dashboard :title="$lead->displayName()" header="Lead details">
    <a href="{{ route('leads.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-800">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
        Back to leads
    </a>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Conversation --}}
        <div class="space-y-6 lg:col-span-2">
            <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">{{ $lead->displayName() }}</h2>
                    <p class="text-sm text-slate-500">{{ $lead->phone }} @if($lead->email)· {{ $lead->email }}@endif</p>
                </div>
                <x-status-badge :status="$lead->status" class="text-sm" />
            </div>

            {{-- Composer --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" x-data="{ channel: 'sms' }">
                <div class="mb-3 inline-flex rounded-lg border border-slate-200 bg-slate-50 p-0.5 text-sm">
                    <button type="button" @click="channel='sms'" :class="channel==='sms' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500'" class="rounded-md px-3 py-1 font-medium">SMS</button>
                    <button type="button" @click="channel='whatsapp'" :class="channel==='whatsapp' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500'" class="rounded-md px-3 py-1 font-medium">WhatsApp</button>
                    <button type="button" @click="channel='note'" :class="channel==='note' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500'" class="rounded-md px-3 py-1 font-medium">Note</button>
                </div>
                <form method="POST" action="{{ route('leads.messages.store', $lead) }}">
                    @csrf
                    <input type="hidden" name="channel" x-model="channel">
                    <textarea name="body" rows="3" required
                              x-bind:placeholder="channel==='note' ? 'Add a private note…' : 'Type your message…'"
                              class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old('body') }}</textarea>
                    @error('body')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-xs text-slate-400" x-show="channel!=='note'">Sends via Twilio to {{ $lead->phone }}</span>
                        <button class="ml-auto rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700"
                                x-text="channel==='note' ? 'Save note' : 'Send message'">Send</button>
                    </div>
                </form>
            </div>

            {{-- Timeline --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-4 font-semibold text-slate-900">Activity</h3>
                <div class="space-y-4">
                    @forelse($lead->interactions as $interaction)
                        @if($interaction->direction === 'system')
                            <div class="flex items-center gap-2 text-xs text-slate-400">
                                <span class="h-px flex-1 bg-slate-100"></span>
                                <span>{{ $interaction->body }} · {{ $interaction->created_at->diffForHumans() }}</span>
                                <span class="h-px flex-1 bg-slate-100"></span>
                            </div>
                        @elseif($interaction->channel === 'note')
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-3">
                                <p class="text-sm text-amber-900">{{ $interaction->body }}</p>
                                <p class="mt-1 text-xs text-amber-600">Note by {{ $interaction->user->name ?? 'team' }} · {{ $interaction->created_at->diffForHumans() }}</p>
                            </div>
                        @else
                            <div class="flex {{ $interaction->direction === 'outbound' ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-[80%]">
                                    <div class="rounded-2xl px-4 py-2 text-sm {{ $interaction->direction === 'outbound' ? 'rounded-tr-sm bg-brand-600 text-white' : 'rounded-tl-sm bg-slate-100 text-slate-800' }}">
                                        {{ $interaction->body }}
                                    </div>
                                    <p class="mt-1 px-1 text-[11px] text-slate-400 {{ $interaction->direction === 'outbound' ? 'text-right' : '' }}">
                                        {{ strtoupper($interaction->channel) }} · {{ $interaction->created_at->diffForHumans() }}
                                        @if($interaction->status) · {{ $interaction->status }}@endif
                                    </p>
                                </div>
                            </div>
                        @endif
                    @empty
                        <p class="py-8 text-center text-sm text-slate-400">No activity yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Details sidebar --}}
        <div class="space-y-6">
            <form method="POST" action="{{ route('leads.update', $lead) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                @csrf @method('PATCH')
                <h3 class="mb-4 font-semibold text-slate-900">Details</h3>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-500">Name</label>
                        <input name="name" value="{{ old('name', $lead->name) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500">Email</label>
                        <input name="email" type="email" value="{{ old('email', $lead->email) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500">Status</label>
                        <select name="status" class="mt-1 w-full rounded-lg border-slate-300 text-sm capitalize shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected($lead->status === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500">Assigned to</label>
                        <select name="assigned_user_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Unassigned</option>
                            @foreach($teamMembers as $member)
                                <option value="{{ $member->id }}" @selected($lead->assigned_user_id === $member->id)>{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500">Booking date</label>
                        <input name="booking_date" type="datetime-local" value="{{ old('booking_date', $lead->booking_date?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500">Estimated value (£)</label>
                        <input name="estimated_value" type="number" step="0.01" min="0" value="{{ old('estimated_value', $lead->estimated_value) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500">Notes</label>
                        <textarea name="notes" rows="3" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old('notes', $lead->notes) }}</textarea>
                    </div>
                </div>

                <button class="mt-4 w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save changes</button>
            </form>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-3 font-semibold text-slate-900">Booking link</h3>
                <div x-data="{ copied: false }" class="flex items-center gap-2">
                    <input readonly value="{{ $currentBusiness->bookingLink() }}" class="w-full truncate rounded-lg border-slate-200 bg-slate-50 text-xs text-slate-600">
                    <button @click="navigator.clipboard.writeText('{{ $currentBusiness->bookingLink() }}'); copied = true; setTimeout(() => copied = false, 1500)"
                            class="shrink-0 rounded-lg border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">
                        <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('leads.destroy', $lead) }}" onsubmit="return confirm('Delete this lead? This cannot be undone.')">
                @csrf @method('DELETE')
                <button class="w-full rounded-lg border border-rose-200 px-4 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50">Delete lead</button>
            </form>
        </div>
    </div>
</x-layouts.dashboard>
