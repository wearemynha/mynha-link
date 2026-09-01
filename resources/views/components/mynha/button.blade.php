@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'primary',
    'size' => null,
    'iconOnly' => false,
])

@php
    $classes = [
        'mynha-button',
        'mynha-button--'.$variant,
        'mynha-button--'.$size => filled($size),
        'mynha-button--icon' => $iconOnly,
    ];
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
