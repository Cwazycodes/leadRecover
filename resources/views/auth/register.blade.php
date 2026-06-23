<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-xl font-bold text-slate-900">Start recovering missed calls</h2>
        <p class="mt-1 text-sm text-slate-500">{{ config('leadrecover.trial_days') }}-day free trial · no card required</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Business name -->
        <div>
            <x-input-label for="business_name" :value="__('Business name')" />
            <x-text-input id="business_name" class="block mt-1 w-full" type="text" name="business_name" :value="old('business_name')" required autofocus />
            <x-input-error :messages="$errors->get('business_name')" class="mt-2" />
        </div>

        <!-- Industry -->
        <div class="mt-4">
            <x-input-label for="industry" :value="__('Industry')" />
            <select id="industry" name="industry" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach(config('leadrecover.industries') as $key => $industry)
                    <option value="{{ $key }}" @selected(old('industry') === $key)>{{ $industry['label'] }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('industry')" class="mt-2" />
        </div>

        <!-- Your name -->
        <div class="mt-4">
            <x-input-label for="name" :value="__('Your name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6 flex items-center justify-between">
            <a class="text-sm text-gray-600 underline hover:text-gray-900" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>
            <x-primary-button>{{ __('Create account') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
