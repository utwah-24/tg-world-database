<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Promotion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PromotionService
{
    /**
     * A promotion is effective only when status is active and today is within its date range.
     */
    public static function isEffective(Promotion $promotion, ?Carbon $asOf = null): bool
    {
        if ($promotion->status !== 'active') {
            return false;
        }

        return self::isWithinDateRange($promotion, $asOf);
    }

    public static function isWithinDateRange(Promotion $promotion, ?Carbon $asOf = null): bool
    {
        $day = ($asOf ?? Carbon::today())->copy()->startOfDay();

        if (! $promotion->start_date || ! $promotion->end_date) {
            return false;
        }

        return $promotion->start_date->copy()->startOfDay()->lte($day)
            && $promotion->end_date->copy()->startOfDay()->gte($day);
    }

    /**
     * Sync promotion status from date ranges and reset affected cars when promos lapse.
     */
    public static function syncStatuses(?Carbon $asOf = null): int
    {
        $today = ($asOf ?? Carbon::today())->copy()->startOfDay();
        $changed = 0;

        Promotion::query()->get()->each(function (Promotion $promotion) use ($today, &$changed) {
            $shouldBeActive = $promotion->start_date
                && $promotion->end_date
                && $promotion->start_date->copy()->startOfDay()->lte($today)
                && $promotion->end_date->copy()->startOfDay()->gte($today);

            $newStatus = $shouldBeActive ? 'active' : 'inactive';

            if ($promotion->status !== $newStatus) {
                DB::table('promotions')
                    ->where('promoID', $promotion->promoID)
                    ->update(['status' => $newStatus, 'updated_at' => now()]);

                $promotion->status = $newStatus;
                $changed++;

                if ($newStatus === 'inactive') {
                    self::resetCarsForPromotion($promotion);
                }
            }
        });

        // Cars may still have promo_set on while all linked promos are inactive
        self::resetCarsWithoutEffectivePromotions();

        return $changed;
    }

    /**
     * Turn off promo pricing on all cars linked to an inactive promotion.
     */
    public static function resetCarsForPromotion(Promotion $promotion): void
    {
        $promotion->cars()->each(function (Car $car): void {
            $car->refreshPromotionState();
        });
    }

    /**
     * Ensure cars with no effective promotions revert to original pricing.
     */
    public static function resetCarsWithoutEffectivePromotions(): void
    {
        Car::query()
            ->where('promo_set', true)
            ->with('promotions')
            ->chunkById(100, function ($cars): void {
                foreach ($cars as $car) {
                    if (! $car->hasEffectivePromotion()) {
                        $car->clearPromotionPricing();
                    }
                }
            }, 'car_id');
    }

    /**
     * IDs of promotions that are currently effective (active + within date range).
     */
    public static function effectivePromoIds(?Carbon $asOf = null): array
    {
        return Promotion::query()
            ->where('status', 'active')
            ->get()
            ->filter(fn (Promotion $promo) => self::isWithinDateRange($promo, $asOf))
            ->pluck('promoID')
            ->all();
    }
}
