@props([
    'type' => 'info',
    'dismissible' => false,
])

@php
    $role = in_array($type, ['danger', 'warning'], true) ? 'alert' : 'status';
@endphp

<div role="{{ $role }}" {{ $attributes->class([
    'alert',
    'mynha-alert',
    'mynha-alert--'.$type,
    'alert-dismissible fade show' => $dismissible,
]) }}>
    <div class="mynha-alert__content">{{ $slot }}</div>
    @if($dismissible)
        <button type="button" class="mynha-alert__close" data-bs-dismiss="alert"
                aria-label="{{ __('Close') }}">
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
    @endif
</div>
