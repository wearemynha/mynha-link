@props(['status'])

@if ($status)
    <x-mynha.alert type="success" {{ $attributes }}>{{ $status }}</x-mynha.alert>
@endif
