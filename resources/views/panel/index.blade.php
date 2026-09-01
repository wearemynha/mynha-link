@extends('layouts.sidebar')

@section('content')
<div class="container-fluid content-inner mynha-dashboard-content">
    <x-mynha.page-header class="mynha-dashboard-title" icon="bi-menu-up" :title="__('messages.Dashboard')" />

    <div class="mynha-stat-grid mynha-stat-grid-two">
        @include('components.dashboard-stat', ['label' => __('messages.Total Links:'), 'value' => $links, 'icon' => 'bi-link'])
        @include('components.dashboard-stat', ['label' => __('messages.Link Clicks:'), 'value' => $clicks, 'icon' => 'bi-eye'])
    </div>

    <section class="card mynha-dashboard-links" aria-labelledby="top-links-heading">
        <div class="card-body">
            <div class="mynha-dashboard-section-heading">
                <h3 id="top-links-heading">{{ __('messages.Top Links:') }}</h3>
                <a class="btn btn-primary" href="{{ url('/studio/links') }}">{{ __('messages.View/Edit Links') }}</a>
            </div>
            @php
                $visibleTopLinks = $toplinks->filter(fn ($link) => $link->name !== 'phone' && $link->name !== 'heading' && $link->button_id !== 96);
            @endphp
            @if($visibleTopLinks->isEmpty())
                <x-mynha.empty-state class="mynha-dashboard-empty">
                    <p>{{ __('messages.You haven’t added any links yet') }}</p>
                </x-mynha.empty-state>
            @else
                <ol class="list-group list-group-numbered">
                    @foreach($visibleTopLinks as $link)
                        <li class="list-group-item d-flex align-items-center gap-3">
                            <div class="mynha-dashboard-link-info">
                                <strong>{{ $link->title }}</strong>
                                <span>{{ $link->link }}</span>
                            </div>
                            <span class="badge bg-primary rounded-pill">{{ $link->click_number }} — {{ __('messages.clicks') }}</span>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </section>

    @if(auth()->user()->role === 'admin' && !config('linkstack.single_user_mode'))
        <section aria-labelledby="site-statistics-heading">
            <h3 id="site-statistics-heading">{{ __('messages.Site statistics:') }}</h3>
            <div class="mynha-stat-grid">
                @include('components.dashboard-stat', ['label' => __('messages.Total links'), 'value' => $siteLinks, 'icon' => 'bi-share'])
                @include('components.dashboard-stat', ['label' => __('messages.Total clicks'), 'value' => $siteClicks, 'icon' => 'bi-eye'])
                @include('components.dashboard-stat', ['label' => __('messages.Total users'), 'value' => $userNumber, 'icon' => 'bi-people'])
            </div>
        </section>

        <div class="mynha-stat-grid mynha-stat-grid-two">
            <section class="card" aria-labelledby="registrations-heading">
                <div class="card-body">
                    <h3 id="registrations-heading">{{ __('messages.Registrations:') }}</h3>
                    <dl class="mynha-dashboard-periods">
                        <div><dt>{{ __('messages.Last 30 days') }}</dt><dd>{{ $lastMonthCount }}</dd></div>
                        <div><dt>{{ __('messages.Last 7 days') }}</dt><dd>{{ $lastWeekCount }}</dd></div>
                        <div><dt>{{ __('messages.Last 24 hours') }}</dt><dd>{{ $last24HrsCount }}</dd></div>
                    </dl>
                </div>
            </section>
            <section class="card" aria-labelledby="active-users-heading">
                <div class="card-body">
                    <h3 id="active-users-heading">{{ __('messages.Active users:') }}</h3>
                    <dl class="mynha-dashboard-periods">
                        <div><dt>{{ __('messages.Last 30 days') }}</dt><dd>{{ $updatedLast30DaysCount }}</dd></div>
                        <div><dt>{{ __('messages.Last 7 days') }}</dt><dd>{{ $updatedLast7DaysCount }}</dd></div>
                        <div><dt>{{ __('messages.Last 24 hours') }}</dt><dd>{{ $updatedLast24HrsCount }}</dd></div>
                    </dl>
                </div>
            </section>
        </div>
    @endif
</div>
@endsection
