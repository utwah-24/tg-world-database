@php
    /** @var \App\Models\Content $record */
    $record = $getRecord();
    $videoName = filled($record->content_video) ? basename($record->content_video) : null;
@endphp

<div class="car-mobile-card">
    <div class="car-mobile-card__hero">
        <div class="car-mobile-card__photo car-mobile-card__photo--empty car-mobile-card__icon">▶</div>

        <div class="car-mobile-card__hero-body">
            <div class="car-mobile-card__name">{{ $record->content_name }}</div>
            <div class="car-mobile-card__meta">
                Content #{{ $record->contentID }}
                @if (filled($record->duration))
                    · {{ $record->duration }}
                @endif
            </div>
            <div class="car-mobile-card__price">
                <span class="car-mobile-card__price-now">{{ $videoName ?: 'No video uploaded' }}</span>
            </div>
        </div>
    </div>

    <div class="car-mobile-card__badges">
        <span class="car-mobile-card__badge {{ $record->car ? 'is-success' : '' }}">
            {{ $record->car?->car_name ?: 'No linked car' }}
        </span>
    </div>

    <dl class="car-mobile-card__details">
        <div>
            <dt>Duration</dt>
            <dd>{{ $record->duration ?: '—' }}</dd>
        </div>
        <div>
            <dt>Created</dt>
            <dd>{{ $record->created_at?->format('d M Y') ?: '—' }}</dd>
        </div>
    </dl>
</div>
