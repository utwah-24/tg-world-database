@php
    /** @var \App\Models\Promotion $record */
    $record = $getRecord();
    $photo = collect($record->promo_pic_urls ?? [])->filter()->first();
    $active = $record->status === 'active';
@endphp

<div class="car-mobile-card">
    <div class="car-mobile-card__hero">
        @if ($photo)
            <img src="{{ $photo }}" alt="{{ $record->promo_name }}" class="car-mobile-card__photo">
        @else
            <div class="car-mobile-card__photo car-mobile-card__photo--empty">No photo</div>
        @endif

        <div class="car-mobile-card__hero-body">
            <div class="car-mobile-card__name">{{ $record->promo_name }}</div>
            <div class="car-mobile-card__meta">Promotion #{{ $record->promoID }}</div>
            <div class="car-mobile-card__price">
                <span class="car-mobile-card__price-now">{{ $record->price_reduction_label }} off</span>
            </div>
        </div>
    </div>

    <div class="car-mobile-card__badges">
        <span class="car-mobile-card__badge {{ $active ? 'is-success' : 'is-danger' }}">
            {{ $active ? 'Active' : 'Inactive' }}
        </span>
    </div>

    <dl class="car-mobile-card__details">
        <div>
            <dt>Starts</dt>
            <dd>{{ $record->start_date?->format('d M Y') ?: '—' }}</dd>
        </div>
        <div>
            <dt>Ends</dt>
            <dd>{{ $record->end_date?->format('d M Y') ?: '—' }}</dd>
        </div>
        <div>
            <dt>Discount</dt>
            <dd>{{ $record->price_reduction_label }}</dd>
        </div>
        <div>
            <dt>Created</dt>
            <dd>{{ $record->created_at?->format('d M Y') ?: '—' }}</dd>
        </div>
    </dl>
</div>
