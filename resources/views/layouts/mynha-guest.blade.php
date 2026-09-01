<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    @include('layouts.analytics')
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/mynha-assets/mynha-icon-preto-verde.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/external-dependencies/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/mynha-assets/mynha.css') }}">
    <script src="{{ asset('assets/mynha-assets/mynha.js') }}" defer></script>
</head>
<body class="mynha-ui mynha-auth">
    <main class="mynha-auth-main">
        {{ $slot }}
    </main>
</body>
</html>
