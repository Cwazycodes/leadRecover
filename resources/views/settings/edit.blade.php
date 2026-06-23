<x-layouts.dashboard title="Settings" header="Settings">
    <div x-data="{ tab: 'profile' }" class="grid gap-6 lg:grid-cols-4">
        {{-- Tabs --}}
        <nav class="flex gap-2 overflow-x-auto lg:flex-col">
            @foreach(['profile' => 'Business profile', 'templates' => 'Messaging', 'booking' => 'Booking', 'hours' => 'Opening hours'] as $key => $label)
                <button @click="tab='{{ $key }}'" :class="tab==='{{ $key }}' ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-slate-600 hover:bg-slate-100'"
                        class="whitespace-nowrap rounded-lg px-3 py-2 text-left text-sm">{{ $label }}</button>
            @endforeach
        </nav>

        <div class="lg:col-span-3">
            {{-- Profile --}}
            <form x-show="tab==='profile'" method="POST" action="{{ route('settings.profile') }}" enctype="multipart/form-data"
                  class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf @method('PUT')
                <h2 class="text-lg font-semibold text-slate-900">Business profile</h2>

                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-xl bg-slate-100">
                        @if($business->logo_path)
                            <img src="{{ Storage::url($business->logo_path) }}" alt="Logo" class="h-full w-full object-cover">
                        @else
                            <span class="text-2xl font-bold text-slate-400">{{ strtoupper(substr($business->name,0,1)) }}</span>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Logo</label>
                        <input type="file" name="logo" accept="image/*" class="mt-1 text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-medium">
                        @error('logo')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Business name</label>
                        <input name="name" value="{{ old('name', $business->name) }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        @error('name')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Industry</label>
                        <select name="industry" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            @foreach($industries as $key => $industry)
                                <option value="{{ $key }}" @selected($business->industry === $key)>{{ $industry['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Contact email</label>
                        <input name="email" type="email" value="{{ old('email', $business->email) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Public phone</label>
                        <input name="phone" value="{{ old('phone', $business->phone) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Twilio number <span class="text-slate-400">(receives calls)</span></label>
                        <input name="twilio_number" value="{{ old('twilio_number', $business->twilio_number) }}" placeholder="+447700900123" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Forward calls to</label>
                        <input name="forward_to_number" value="{{ old('forward_to_number', $business->forward_to_number) }}" placeholder="+447700900999" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Timezone</label>
                        <select name="timezone" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            @foreach($timezones as $tz)
                                <option value="{{ $tz }}" @selected($business->timezone === $tz)>{{ $tz }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="pt-2"><button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save profile</button></div>
            </form>

            {{-- Templates --}}
            <form x-show="tab==='templates'" x-cloak method="POST" action="{{ route('settings.templates') }}"
                  class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf @method('PUT')
                <h2 class="text-lg font-semibold text-slate-900">Messaging</h2>
                <p class="rounded-lg bg-slate-50 p-3 text-xs text-slate-500">Available tokens: <code class="font-mono text-brand-600">{{ '{{name}}' }}</code>, <code class="font-mono text-brand-600">{{ '{{business}}' }}</code>, <code class="font-mono text-brand-600">{{ '{{booking_link}}' }}</code></p>

                <label class="flex items-center gap-3">
                    <input type="checkbox" name="auto_respond_enabled" value="1" @checked($business->auto_respond_enabled) class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <span class="text-sm text-slate-700">Automatically text back missed callers</span>
                </label>
                <label class="flex items-center gap-3">
                    <input type="checkbox" name="whatsapp_enabled" value="1" @checked($business->whatsapp_enabled) class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <span class="text-sm text-slate-700">Use WhatsApp as the primary channel (falls back to SMS)</span>
                </label>

                <div>
                    <label class="block text-sm font-medium text-slate-700">SMS template</label>
                    <textarea name="sms_template" rows="3" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old('sms_template', $business->sms_template) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">WhatsApp template</label>
                    <textarea name="whatsapp_template" rows="3" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old('whatsapp_template', $business->whatsapp_template) }}</textarea>
                </div>
                <div class="pt-2"><button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save messaging</button></div>
            </form>

            {{-- Booking --}}
            <form x-show="tab==='booking'" x-cloak method="POST" action="{{ route('settings.booking') }}"
                  x-data="{ type: '{{ old('booking_type', $business->booking_type) }}' }"
                  class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf @method('PUT')
                <h2 class="text-lg font-semibold text-slate-900">Booking</h2>

                <div class="space-y-2">
                    @foreach(['internal' => 'Built-in booking page', 'url' => 'My own booking URL', 'calendly' => 'Calendly'] as $value => $label)
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 p-3" :class="type==='{{ $value }}' && 'border-brand-400 bg-brand-50'">
                            <input type="radio" name="booking_type" value="{{ $value }}" x-model="type" class="text-brand-600 focus:ring-brand-500">
                            <span class="text-sm font-medium text-slate-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                <div x-show="type==='url'">
                    <label class="block text-sm font-medium text-slate-700">Booking URL</label>
                    <input name="booking_url" value="{{ old('booking_url', $business->booking_url) }}" placeholder="https://…" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    @error('booking_url')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div x-show="type==='calendly'">
                    <label class="block text-sm font-medium text-slate-700">Calendly URL</label>
                    <input name="calendly_url" value="{{ old('calendly_url', $business->calendly_url) }}" placeholder="https://calendly.com/…" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    @error('calendly_url')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div x-show="type==='internal'" class="rounded-lg bg-slate-50 p-3 text-sm text-slate-600">
                    Your built-in booking page: <a href="{{ route('book', $business) }}" target="_blank" class="font-medium text-brand-600 hover:underline">{{ route('book', $business) }}</a>
                </div>
                <div class="pt-2"><button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save booking</button></div>
            </form>

            {{-- Hours --}}
            <form x-show="tab==='hours'" x-cloak method="POST" action="{{ route('settings.hours') }}"
                  class="space-y-3 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf @method('PUT')
                <h2 class="text-lg font-semibold text-slate-900">Opening hours</h2>
                @php $hours = $business->opening_hours ?? []; @endphp
                @foreach($days as $day)
                    @php $d = $hours[$day] ?? ['open' => '09:00', 'close' => '17:00', 'closed' => false]; @endphp
                    <div class="flex flex-wrap items-center gap-3 border-b border-slate-100 pb-3">
                        <span class="w-24 text-sm font-medium capitalize text-slate-700">{{ $day }}</span>
                        <label class="flex items-center gap-2 text-xs text-slate-500">
                            <input type="checkbox" name="hours[{{ $day }}][closed]" value="1" @checked($d['closed'] ?? false) class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                            Closed
                        </label>
                        <input type="time" name="hours[{{ $day }}][open]" value="{{ $d['open'] ?? '09:00' }}" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        <span class="text-slate-400">—</span>
                        <input type="time" name="hours[{{ $day }}][close]" value="{{ $d['close'] ?? '17:00' }}" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                @endforeach
                <div class="pt-2"><button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save hours</button></div>
            </form>
        </div>
    </div>
</x-layouts.dashboard>
