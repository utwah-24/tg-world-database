@php
    /** @var \App\Models\Car $record */
    $record = $getRecord();
    $photo = collect($record->car_pic_urls ?? [])->filter()->first();
    $type = strtolower((string) $record->type);
    $typeLabel = match ($type) {
        'truck' => 'Truck',
        'suv' => 'SUV',
        'third_party' => 'Third Party',
        'sedan' => 'Sedan',
        'van' => 'Van',
        'pickup' => 'Pickup',
        default => $record->type ? ucwords(str_replace('_', ' ', (string) $record->type)) : '—',
    };
    $conditionLabel = match ($record->condition) {
        'new' => 'New',
        'second_hand' => 'Second Hand',
        'third_party' => 'Third Party',
        default => '—',
    };
    $isSold = $record->is_sold === 'sold';
    $isComingSoon = $record->is_coming_soon === 'set';
    $isPromo = (bool) $record->promo_set && filled($record->promo_price);
    $registered = $record->registration === 'registered';
    $location = $record->in_dar ? 'Dar es Salaam' : ($record->location ?: '—');
    $promos = $record->promotions->pluck('promo_name')->filter()->implode(', ');
    $details = array_filter([
        'Color' => $record->color,
        'Chassis' => $record->chassis,
        'Mileage' => $record->mileage,
        'Reg. number' => $record->registration_number,
        'Available' => $record->total_available,
        'Company' => $record->company?->name ?? $record->company_label,
        'Brand' => $record->brand?->name ?? $record->brand_label,
        'Model' => $record->vehicleModel?->name ?? $record->model_label,
        'Promotions' => $promos,
    ], fn ($value) => $value !== null && $value !== '');
@endphp

<div class="car-mobile-card">
    <div class="car-mobile-card__hero">
        @if ($photo)
            <img src="{{ $photo }}" alt="{{ $record->car_name }}" class="car-mobile-card__photo">
        @else
            <div class="car-mobile-card__photo car-mobile-card__photo--empty">No photo</div>
        @endif

        <div class="car-mobile-card__hero-body">
            <div class="car-mobile-card__name">{{ $record->car_name }}</div>
            <div class="car-mobile-card__meta">
                {{ $record->year ?: '—' }}
                ·
                {{ $typeLabel }}
            </div>
            <div class="car-mobile-card__price">
                @if ($isPromo)
                    <span class="car-mobile-card__price-now">{{ $record->promo_price }}</span>
                    <span class="car-mobile-card__price-old">{{ $record->car_price }}</span>
                @else
                    <span class="car-mobile-card__price-now">{{ $record->car_price ?: '—' }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="car-mobile-card__badges">
        <span class="car-mobile-card__badge {{ $isSold ? 'is-danger' : 'is-success' }}">
            {{ $isSold ? 'Sold' : 'Available' }}
        </span>
        <span class="car-mobile-card__badge">{{ $conditionLabel }}</span>
        <span class="car-mobile-card__badge {{ $registered ? 'is-success' : '' }}">
            {{ $registered ? 'Registered' : 'Unregistered' }}
        </span>
        @if ($record->test_drive_available)
            <span class="car-mobile-card__badge is-success">Test drive</span>
        @endif
        @if ($isPromo)
            <span class="car-mobile-card__badge is-success">Promo</span>
        @endif
        @if ($isComingSoon)
            <span class="car-mobile-card__badge is-warning">
                {{ $record->arrival_date ? 'Arrives '.$record->arrival_date->format('d M Y') : 'Coming soon' }}
            </span>
        @endif
    </div>

    <dl class="car-mobile-card__details">
        <div>
            <dt>Location</dt>
            <dd>{{ $location }}</dd>
        </div>
        @foreach ($details as $label => $value)
            <div>
                <dt>{{ $label }}</dt>
                <dd>{{ $value }}</dd>
            </div>
        @endforeach
    </dl>
</div>
