@props(['elevated' => false])

<div {{ $attributes->class([
    'mynha-card',
    'mynha-card--elevated' => $elevated,
]) }}>{{ $slot }}</div>
