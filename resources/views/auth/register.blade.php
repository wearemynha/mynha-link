<x-guest-layout brand="mynha">
    <section class="mynha-auth-card mynha-auth-card--wide" aria-labelledby="register-heading">
        <a href="{{ url('') }}" class="mynha-auth-brand">
            <img src="{{ asset('assets/mynha-assets/mynha-icon-preto-verde.svg') }}" alt="" width="48" height="48">
            <span>{{ config('app.name') }}</span>
        </a>

        <h1 id="register-heading" class="mynha-auth-heading">{{ __('messages.Sign Up') }}</h1>
        <p class="mynha-auth-description">{{ __('messages.Register to stay connected') }}.</p>

        <x-auth-session-status class="mynha-auth-notice" role="status" :status="session('status')" />
        <x-auth-validation-errors class="mynha-auth-notice" role="alert" :errors="$errors" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <x-mynha.field for="name" :label="__('messages.Display Name')" :error="$errors->first('name')">
                <x-mynha.input type="text" id="name" name="name" :value="old('name')"
                               :invalid="$errors->has('name')" required autofocus autocomplete="name" />
            </x-mynha.field>

            <x-mynha.field for="littlelink_name" :label="__('messages.Page URL')">
                <div class="mynha-auth-url-field">
                    <span aria-hidden="true">{{ str_replace(['http://', 'https://'], '', url('')) }}/@</span>
                    <x-mynha.input type="text" id="littlelink_name" name="littlelink_name"
                                   :value="old('littlelink_name')" required maxlength="50"
                                   autocomplete="username" />
                </div>
                @include('auth.url-validation')
            </x-mynha.field>

            <x-mynha.field for="email" :label="__('messages.Email')" :error="$errors->first('email')">
                <x-mynha.input type="email" id="email" name="email" :value="old('email')"
                               :invalid="$errors->has('email')" required autocomplete="email" />
            </x-mynha.field>

            <x-mynha.field for="password" :label="__('messages.Password')" :error="$errors->first('password')">
                <x-password-field id="password" name="password" minlength="8"
                                  :invalid="$errors->has('password')" required autocomplete="new-password" />
            </x-mynha.field>

            <label class="mynha-auth-remember mynha-auth-register-remember" for="remember_me">
                <input type="checkbox" name="remember" id="remember_me" @checked(old('remember'))>
                <span>{{ __('messages.Remember Me') }}</span>
            </label>

            <div class="mynha-auth-actions">
                <x-mynha.button id="submit-btn" type="submit">{{ __('messages.Sign Up') }}</x-mynha.button>
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

            <p class="mynha-auth-footer">
                {{ __('messages.Already have an account?') }}
                <a href="{{ route('login') }}">{{ __('messages.Click here to sign in') }}</a>.
            </p>
        </form>
    </section>
</x-guest-layout>
