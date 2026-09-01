<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, follow">
    <title>{{ config('app.name') }} — {{ __('messages.Example page') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/mynha-assets/mynha-site-favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/external-dependencies/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/mynha-assets/mynha.css') }}">
</head>
<body class="mynha-ui mynha-demo">
    <main class="mynha-demo-content">
        <img class="mynha-demo-logo" src="{{ asset('assets/mynha-assets/mynha-icon-preto-verde.svg') }}" alt="Mynha" width="128" height="128">
        <h1>{{ config('app.name') }}</h1>
        <p>{{ __('messages.Example page') }}</p>
        <div class="mynha-demo-links">
            @php
                $buttons = config('advanced-config.use_custom_buttons') == 'true'
                    ? config('advanced-config.buttons', [])
                    : (require storage_path('templates/advanced-config.php'))['buttons'];
            @endphp
            @foreach($buttons as $button)
                @if($button['button'] === 'heading')
                    <h2>{{ $button['title'] }}</h2>
                @elseif($button['button'] === 'space')
                    <div class="mynha-demo-spacer" aria-hidden="true"></div>
                @else
                    <a class="mynha-button mynha-demo-link" href="{{ $button['link'] }}" target="_blank" rel="noopener noreferrer nofollow">
                        @if(in_array(parse_url($button['link'], PHP_URL_HOST), ['mynha.com.br', 'www.mynha.com.br'], true))
                            <img src="{{ asset('assets/mynha-assets/mynha-site-favicon.png') }}" alt="" width="24" height="24">
                        @elseif($button['button'] === 'instagram')
                            <i class="bi bi-instagram" aria-hidden="true"></i>
                        @elseif(($button['icon'] ?? '') === 'fa-pen-nib')
                            <i class="bi bi-vector-pen" aria-hidden="true"></i>
                        @else
                            <i class="bi bi-link-45deg" aria-hidden="true"></i>
                        @endif
                        <span>{{ $button['title'] ?: ucfirst($button['button']) }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </main>
</body>
</html>
