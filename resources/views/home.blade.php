<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('linkstack.custom_meta_tags') == 'true' && config('advanced-config.title') != '' ? config('advanced-config.title') : config('app.name') }}</title>
    @php
        $homeMessage = !$message || $message->home_message === 'default'
            ? __('messages.HOME.MESSAGE')
            : $message->home_message;
    @endphp
    <meta property="og:url" content="{{ url('') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ config('app.name') }}">
    <meta property="og:description" content="{{ strip_tags($homeMessage) }}">
    <meta property="og:image" content="{{ asset('assets/mynha-assets/mynha-icon-preto-verde.svg') }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ config('app.name') }}">
    <meta name="twitter:description" content="{{ strip_tags($homeMessage) }}">
    <meta name="twitter:image" content="{{ asset('assets/mynha-assets/mynha-icon-preto-verde.svg') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/mynha-assets/mynha-icon-preto-verde.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/mynha-assets/mynha.css') }}">
</head>
<body class="mynha-ui mynha-home">
    <header class="mynha-home-header">
        <a href="{{ route('panelIndex') }}" class="mynha-home-brand">
            <img src="{{ asset('assets/mynha-assets/mynha-icon-preto-verde.svg') }}" alt="" width="40" height="40">
            <span>{{ config('app.name') }}</span>
        </a>
        <nav class="mynha-home-actions">
            @include('components.home-actions')
        </nav>
    </header>

    <main class="mynha-home-main">
        <section class="mynha-home-intro" aria-labelledby="home-heading">
            <img class="mynha-home-icon" src="{{ asset('assets/mynha-assets/mynha-icon-preto-verde.svg') }}" alt="" width="128" height="128">
            <h1 id="home-heading">{{ config('app.name') }}</h1>
            <div class="mynha-home-description">{!! $homeMessage !!}</div>
            <div class="mynha-home-actions">
                @include('components.home-actions')
            </div>
        </section>
        <section class="mynha-home-preview" aria-label="{{ __('messages.Example page') }}">
            <iframe src="{{ url('/demo-page') }}" title="{{ __('messages.Example page') }}" class="mynha-home-frame"></iframe>
        </section>
    </main>

    <footer class="mynha-home-footer">
        <ul class="mynha-home-footer-links">
            @if(config('linkstack.display_footer') === true)
                @if(config('linkstack.display_footer_home') === true)
                    <li><a href="{{ config('linkstack.home_footer_link') ?: url('') }}">{{ footer('Home') }}</a></li>
                @endif
                @if(config('linkstack.display_footer_terms') === true)
                    <li><a href="{{ url('pages/'.strtolower(footer('Terms'))) }}">{{ footer('Terms') }}</a></li>
                @endif
                @if(config('linkstack.display_footer_privacy') === true)
                    <li><a href="{{ url('pages/'.strtolower(footer('Privacy'))) }}">{{ footer('Privacy') }}</a></li>
                @endif
                @if(config('linkstack.display_footer_contact') === true)
                    <li><a href="{{ url('pages/'.strtolower(footer('Contact'))) }}">{{ footer('Contact') }}</a></li>
                @endif
            @endif
        </ul>
        <p>
            {{ __('messages.Copyright') }} &copy; {{ date('Y') }} {{ config('app.name') }}
            @if(config('linkstack.display_credit_footer') === true)
                — {{ __('messages.Made with') }} ♥ {{ __('messages.by') }}
                <a href="https://linkstack.org/" target="_blank" rel="noopener noreferrer">LinkStack</a>.
            @endif
        </p>
    </footer>
</body>
</html>
