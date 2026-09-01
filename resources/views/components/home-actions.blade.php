@if (Route::has('login'))
    @auth
        <a class="mynha-button" href="{{ url('dashboard') }}">{{ __('messages.Dashboard') }}</a>
    @else
        <a class="mynha-button" href="{{ route('login') }}">{{ __('messages.Log in') }}</a>
        @if(config('linkstack.allow_registration') && !config('linkstack.single_user_mode'))
            <a class="mynha-button mynha-home-register" href="{{ route('register') }}">{{ __('messages.Register') }}</a>
        @endif
    @endauth
@endif
