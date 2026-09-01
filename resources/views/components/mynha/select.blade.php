@props(['invalid' => false])

<select {{ $attributes->class([
    'mynha-select',
    'form-control',
    'is-invalid' => $invalid,
]) }}>{{ $slot }}</select>
