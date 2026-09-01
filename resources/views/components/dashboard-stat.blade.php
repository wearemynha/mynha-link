<x-mynha.card class="card mynha-stat-card">
    <div class="card-body">
        <span class="mynha-stat-icon"><i class="bi {{ $icon }}" aria-hidden="true"></i></span>
        <div>
            <p>{{ $label }}</p>
            <strong class="mynha-stat-value">{{ $value }}</strong>
        </div>
    </div>
</x-mynha.card>
