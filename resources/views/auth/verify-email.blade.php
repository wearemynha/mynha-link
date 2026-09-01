<x-guest-layout brand="mynha">
    <section class="mynha-auth-card" aria-labelledby="verify-email-heading">
        <a href="{{ url('') }}" class="mynha-auth-brand">
            <img src="{{ asset('assets/mynha-assets/mynha-icon-preto-verde.svg') }}" alt="" width="48" height="48">
            <span>{{ config('app.name') }}</span>
        </a>

        <h1 id="verify-email-heading" class="mynha-auth-heading">{{ __('messages.Verification Status') }}</h1>
        <p class="mynha-auth-description">{{ __('messages.auth_thanks') }}</p>

        @if(session('status') === 'verification-link-sent')
            <x-mynha.alert type="success">{{ __('messages.auth_verification') }}</x-mynha.alert>
        @endif

        <div class="mynha-stack">
            <form method="POST" action="{{ route('verification.send') }}" class="mynha-auth-actions">
                @csrf
                <x-mynha.button type="submit">{{ __('messages.Resend Verification Email') }}</x-mynha.button>
            </form>
            <form method="POST" action="{{ route('logout') }}" class="mynha-auth-actions">
                @csrf
                <x-mynha.button type="submit" variant="secondary">{{ __('messages.Log out') }}</x-mynha.button>
            </form>
        </div>
    </section>
</x-guest-layout>
