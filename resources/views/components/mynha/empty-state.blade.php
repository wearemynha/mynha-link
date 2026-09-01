@props(['title' => null])

<div {{ $attributes->class('mynha-empty-state') }}>
    @if($title)
        <h2>{{ $title }}</h2>
    @endif
    {{ $slot }}
</div>
