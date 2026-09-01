@php
    /** @var \App\Models\Order $record */
    $record = $getRecord();
    $photo = collect($record->car_pic_urls ?? [])->filter()->first();
    $money = static fn ($value): string => $value !== null
        ? 'Tshs '.number_format((float) $value, 0, '.', ',')
        : '—';
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
                Order #{{ $record->id }}
                @if (filled($record->year))
                    · {{ $record->year }}
                @endif
            </div>
            <div class="car-mobile-card__price">
                <span class="car-mobile-card__price-now">{{ $money($record->total_amount) }}</span>
            </div>
        </div>
    </div>

    <div class="car-mobile-card__badges">
        <span class="car-mobile-card__badge {{ $record->status ? 'is-success' : 'is-warning' }}">
            {{ $record->status ? 'Approved' : 'Pending' }}
        </span>
        <span class="car-mobile-card__badge">Qty {{ $record->qty ?? '—' }}</span>
        <span class="car-mobile-card__badge {{ filled($record->invoice) ? 'is-success' : '' }}">
            {{ filled($record->invoice) ? 'Invoice attached' : 'No invoice' }}
        </span>
        <span class="car-mobile-card__badge {{ filled($record->receipt) ? 'is-success' : '' }}">
            {{ filled($record->receipt) ? 'Receipt attached' : 'No receipt' }}
        </span>
    </div>

    <dl class="car-mobile-card__details">
        <div>
            <dt>Order date</dt>
            <dd>{{ $record->order_date?->format('d M Y') ?: '—' }}</dd>
        </div>
        <div>
            <dt>Customer</dt>
            <dd>{{ $record->email ?: '—' }}</dd>
        </div>
        <div>
            <dt>Amount paid</dt>
            <dd>{{ $money($record->amount_paid) }}</dd>
        </div>
        <div>
            <dt>Amount due</dt>
            <dd>{{ $money($record->amount_due) }}</dd>
        </div>
        <div>
            <dt>Qty left</dt>
            <dd>{{ $record->total_available ?? '—' }}</dd>
        </div>
        <div>
            <dt>Created</dt>
            <dd>{{ $record->created_at?->format('d M Y') ?: '—' }}</dd>
        </div>
    </dl>
</div>
