@php
    /** @var \App\Models\SoldCar $record */
    $record = $getRecord();
    $photo = collect($record->car_pic_urls ?? [])->filter()->first();
@endphp

<div class="car-mobile-card">
    <div class="car-mobile-card__hero">
        @if ($photo)
            <img src="{{ $photo }}" alt="{{ $record->car_name }}" class="car-mobile-card__photo">
        @else
            <div class="car-mobile-card__photo car-mobile-card__photo--empty">No photo</div>
        @endif

        <div class="car-mobile-card__hero-body">
            <div class="car-mobile-card__name">{{ $record->car_name ?: 'Unnamed car' }}</div>
            <div class="car-mobile-card__meta">
                Sold car #{{ $record->id }}
                @if (filled($record->car_id))
                    · Car #{{ $record->car_id }}
                @endif
            </div>
            <div class="car-mobile-card__price">
                <span class="car-mobile-card__price-now">{{ $record->price_sold ?: 'Price not recorded' }}</span>
            </div>
        </div>
    </div>

    <div class="car-mobile-card__badges">
        <span class="car-mobile-card__badge is-success">Sold</span>
        <span class="car-mobile-card__badge">Qty {{ $record->qty ?? '—' }}</span>
        <span class="car-mobile-card__badge">
            {{ $record->total_available !== null ? $record->total_available.' left' : 'Qty left —' }}
        </span>
    </div>

    <dl class="car-mobile-card__details">
        <div>
            <dt>Sold at</dt>
            <dd>{{ $record->sold_at?->format('d M Y, H:i') ?: '—' }}</dd>
        </div>
        <div>
            <dt>Order ID</dt>
            <dd>{{ $record->order_id ?: '—' }}</dd>
        </div>
        <div>
            <dt>Recorded</dt>
            <dd>{{ $record->created_at?->format('d M Y') ?: '—' }}</dd>
        </div>
    </dl>
</div>
