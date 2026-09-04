<!DOCTYPE html>
@include('layouts.lang')
<head>
  <meta charset="utf-8">
  @include('layouts.analytics')
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @stack('installer-head')
  <title>{{__('messages.LinkStack setup')}}</title>
  <link rel="stylesheet" href="{{ asset('assets/external-dependencies/bootstrap-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/linkstack/css/normalize.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/mynha-assets/mynha.css') }}">
  <script src="{{ asset('assets/mynha-assets/mynha.js') }}" defer></script>
  <link rel="icon" type="image/svg+xml" href="{{ asset('assets/mynha-assets/mynha-icon-preto-verde.svg') }}">
</head>
<body class="mynha-ui mynha-installer">
  @stack('installer-body')
</body>
</html>
