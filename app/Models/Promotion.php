<?php

namespace App\Models;

use App\Services\PromotionService;
use App\Traits\SyncsToRemote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

class Promotion extends Model
{
    use SyncsToRemote;

    protected $primaryKey = 'promoID';

    protected $fillable = [
        'promo_name',
        'price_reduction',
        'promo_pics',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'promo_pics' => 'array',
        'price_reduction' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /** Always include resolved absolute URLs alongside raw paths. */
    protected $appends = ['promo_pic_urls', 'price_reduction_label'];

    public function cars(): BelongsToMany
    {
        return $this->belongsToMany(
            Car::class,
            'car_promotion',
            'promoID',
            'car_id',
            'promoID',
            'car_id',
        )->withTimestamps();
    }

    protected static function booted(): void
    {
        static::saved(function (Promotion $promotion): void {
            if ($promotion->wasChanged('status') && $promotion->status === 'inactive') {
                PromotionService::resetCarsForPromotion($promotion);

                return;
            }

            if (! $promotion->wasChanged('price_reduction') && ! $promotion->wasRecentlyCreated) {
                return;
            }

            $promotion->cars()->each(function (Car $car): void {
                $car->refreshPromoPrice();
            });
        });
    }

    /**
     * Absolute URLs for each promotion image (MEDIA_BASE_URL aware).
     *
     * @return list<string>
     */
    public function getPromoPicUrlsAttribute(): array
    {
        return collect($this->promo_pics ?? [])
            ->filter()
            ->map(fn (string $path) => Car::mediaUrl($path))
            ->values()
            ->all();
    }

    public function getPriceReductionLabelAttribute(): string
    {
        return ((int) $this->price_reduction).'%';
    }

    public function isCurrentlyActive(?Carbon $asOf = null): bool
    {
        return PromotionService::isEffective($this, $asOf);
    }
}
