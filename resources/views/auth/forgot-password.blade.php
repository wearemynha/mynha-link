<x-guest-layout brand="mynha">
    <section class="mynha-auth-card" aria-labelledby="forgot-password-heading">
        <a href="{{ url('') }}" class="mynha-auth-brand">
            <img src="{{ asset('assets/mynha-assets/mynha-icon-preto-verde.svg') }}" alt="" width="48" height="48">
            <span>{{ config('app.name') }}</span>
        </a>

        <h1 id="forgot-password-heading" class="mynha-auth-heading">{{ __('messages.Forgot your password?') }}</h1>
        <p class="mynha-auth-description">{{ __('messages.No problem') }}</p>

        <x-auth-session-status class="mynha-auth-notice" role="status" :status="session('status')" />
        <x-auth-validation-errors class="mynha-auth-notice" role="alert" :errors="$errors" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <x-mynha.field for="email" :label="__('messages.Email')" :error="$errors->first('email')">
                <x-mynha.input id="email" type="email" name="email" :value="old('email')"
                               :invalid="$errors->has('email')" required autofocus autocomplete="email" />
            </x-mynha.field>
            <div class="mynha-auth-actions">
                <x-mynha.button type="submit">{{ __('messages.Email Password Reset Link') }}</x-mynha.button>
            </div>
        </form>
    </section>
</x-guest-layout>
