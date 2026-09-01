<x-guest-layout brand="mynha">
    <section class="mynha-auth-card" aria-labelledby="reset-password-heading">
        <a href="{{ url('') }}" class="mynha-auth-brand">
            <img src="{{ asset('assets/mynha-assets/mynha-icon-preto-verde.svg') }}" alt="" width="48" height="48">
            <span>{{ config('app.name') }}</span>
        </a>

        <h1 id="reset-password-heading" class="mynha-auth-heading">{{ __('messages.Reset Password') }}</h1>
        <p class="mynha-auth-description">{{ __('messages.Enter a new password') }}</p>
        <x-auth-validation-errors class="mynha-auth-notice" role="alert" :errors="$errors" />

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <x-mynha.field for="email" :label="__('messages.Email')" :error="$errors->first('email')">
                <x-mynha.input id="email" type="email" name="email"
                               :value="old('email', $request->email)" :invalid="$errors->has('email')"
                               required autofocus autocomplete="email" />
            </x-mynha.field>

            <x-mynha.field for="password" :label="__('messages.Password')" :error="$errors->first('password')">
                <x-password-field id="password" name="password" :invalid="$errors->has('password')"
                                  required autocomplete="new-password" />
            </x-mynha.field>

            <x-mynha.field for="password_confirmation" :label="__('messages.Confirm Password')">
                <x-password-field id="password_confirmation" name="password_confirmation"
                                  required autocomplete="new-password" />
            </x-mynha.field>

            <div class="mynha-auth-actions">
                <x-mynha.button type="submit">{{ __('messages.Reset Password') }}</x-mynha.button>
            </div>
        </form>
    </section>
</x-guest-layout>
