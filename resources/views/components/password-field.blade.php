@props(['id', 'name', 'invalid' => false])

<div class="mynha-password-field">
    <input id="{{ $id }}" name="{{ $name }}" type="password" {{ $attributes->class([
        'mynha-input',
        'form-control',
        'is-invalid' => $invalid,
    ]) }}>
    <button type="button" class="mynha-password-toggle" hidden
            data-password-toggle
            data-label-show="{{ __('messages.Show password') }}"
            data-label-hide="{{ __('messages.Hide password') }}"
            aria-controls="{{ $id }}" aria-pressed="false"
            aria-label="{{ __('messages.Show password') }}"
            title="{{ __('messages.Show password') }}">
        <i class="bi bi-eye" aria-hidden="true"></i>
    </button>
</div>
