<x-guest-layout brand="mynha">
    <section class="mynha-auth-card" aria-labelledby="confirm-password-heading">
        <a href="{{ url('') }}" class="mynha-auth-brand">
            <img src="{{ asset('assets/mynha-assets/mynha-icon-preto-verde.svg') }}" alt="" width="48" height="48">
            <span>{{ config('app.name') }}</span>
        </a>

        <h1 id="confirm-password-heading" class="mynha-auth-heading">{{ __('messages.Confirm') }}</h1>
        <p class="mynha-auth-description">{{ __('messages.auth_password') }}</p>
        <x-auth-validation-errors class="mynha-auth-notice" role="alert" :errors="$errors" />

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf
            <x-mynha.field for="password" :label="__('messages.Password')" :error="$errors->first('password')">
                <x-password-field id="password" name="password" :invalid="$errors->has('password')"
                                  required autocomplete="current-password" />
            </x-mynha.field>
            <div class="mynha-auth-actions">
                <x-mynha.button type="submit">{{ __('messages.Confirm') }}</x-mynha.button>
            </div>
        </form>
    </section>
</x-guest-layout>
