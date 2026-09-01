@props(['invalid' => false])

<input {{ $attributes->class([
    'mynha-input',
    'form-control',
    'is-invalid' => $invalid,
]) }}>
