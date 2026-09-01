<x-guest-layout brand="mynha">
    <section class="mynha-auth-card" aria-labelledby="login-heading">
        <a href="{{ url('') }}" class="mynha-auth-brand">
            <img src="{{ asset('assets/mynha-assets/mynha-icon-preto-verde.svg') }}" alt="" width="48" height="48">
            <span>{{ config('app.name') }}</span>
        </a>
        <h1 id="login-heading" class="mynha-auth-heading">{{ __('messages.Sign In') }}</h1>
        <p class="mynha-auth-description">{{ __('messages.Login to stay connected') }}.</p>

        <x-auth-session-status class="mynha-auth-notice" role="status" :status="session('status')" />
        <x-auth-validation-errors class="mynha-auth-notice" role="alert" :errors="$errors" />

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <x-mynha.field for="email" :label="__('messages.Email')" :error="$errors->first('email')">
                <x-mynha.input type="email" id="email" name="email" :value="old('email')"
                               :invalid="$errors->has('email')" required autofocus autocomplete="username" />
            </x-mynha.field>

            <x-mynha.field for="password" :label="__('messages.Password')" :error="$errors->first('password')">
                <x-password-field id="password" name="password" :invalid="$errors->has('password')"
                                  required autocomplete="current-password" />
            </x-mynha.field>

            <div class="mynha-auth-options">
                <label class="mynha-auth-remember" for="remember_me">
                    <input type="checkbox" name="remember" id="remember_me" @checked(old('remember'))>
                    <span>{{ __('messages.Remember Me') }}</span>
                </label>
                {{-- Password recovery link intentionally withheld until the flow is ready. --}}
            </div>
            <div class="mynha-auth-actions">
                <x-mynha.button type="submit">{{ __('messages.Sign In') }}</x-mynha.button>
            </div>

            @if(config('linkstack.enable_social_login'))
                <p class="mynha-auth-footer">{{ __('messages.or sign in with other accounts?') }}</p>
                <ul class="mynha-auth-social">
                    @foreach(['facebook', 'twitter', 'google', 'github'] as $provider)
                        @if(!empty(config('services.'.$provider.'.client_id')))
                            <li>
                                <a href="{{ route('social.redirect', $provider) }}" aria-label="{{ ucfirst($provider) }}">
                                    <i class="bi bi-{{ $provider }}" aria-hidden="true"></i>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            @endif
            @if(config('linkstack.allow_registration') and !config('linkstack.single_user_mode'))
                <p class="mynha-auth-footer">
                    {{ __('messages.Don’t have an account?') }}
                    <a href="{{ route('register') }}">{{ __('messages.Click here to sign up') }}</a>.
                </p>
            @endif
        </form>
    </section>
</x-guest-layout>
