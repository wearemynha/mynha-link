@props(['value'])

<label {{ $attributes->class('mynha-label') }}>
    {{ $value ?? $slot }}
</label>
