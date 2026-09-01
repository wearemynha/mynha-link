@if ($message = Session::get('success'))
<x-mynha.alert type="success" dismissible><strong>{{ $message }}</strong></x-mynha.alert>
@endif


@if ($message = Session::get('error'))
<x-mynha.alert type="danger" dismissible><strong>{{ $message }}</strong></x-mynha.alert>
@endif


@if ($message = Session::get('warning'))
<x-mynha.alert type="warning" dismissible><strong>{{ $message }}</strong></x-mynha.alert>
@endif


@if ($message = Session::get('info'))
<x-mynha.alert type="info" dismissible><strong>{{ $message }}</strong></x-mynha.alert>
@endif


@if ($errors->any())
<x-mynha.alert type="danger">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</x-mynha.alert>

@endif
