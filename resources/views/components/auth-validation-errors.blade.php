@props(['errors'])

@if ($errors->any())
    <x-mynha.alert type="danger" {{ $attributes }}>
        <strong>
            {{ __('Whoops! Something went wrong.') }}
        </strong>

        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-mynha.alert>
@endif
