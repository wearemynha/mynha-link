@props(['title', 'description' => null, 'icon' => null])

<header {{ $attributes->class('mynha-page-header') }}>
    <h1 class="mynha-page-header__title">
        @if($icon)<i class="bi {{ $icon }}" aria-hidden="true"></i>@endif
        {{ $title }}
    </h1>
    @isset($actions)
        <div class="mynha-page-header__actions">{{ $actions }}</div>
    @endisset
    @if($description)
        <p class="mynha-page-header__description">{{ $description }}</p>
    @endif
</header>
