@props([
    'for',
    'label',
    'hint' => null,
    'error' => null,
])

<div {{ $attributes->class('mynha-field') }}>
    <label for="{{ $for }}">{{ $label }}</label>
    {{ $slot }}
    @if($hint)
        <p id="{{ $for }}-hint" class="mynha-field__hint">{{ $hint }}</p>
    @endif
    @if($error)
        <p id="{{ $for }}-error" class="mynha-field__error" role="alert">{{ $error }}</p>
    @endif
</div>
