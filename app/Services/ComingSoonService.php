<?php

namespace App\Services;

use App\Models\Car;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ComingSoonService
{
    public static function isExpired(?string $isComingSoon, mixed $arrivalDate, ?Carbon $asOf = null): bool
    {
        if ($isComingSoon !== 'set' || blank($arrivalDate)) {
            return false;
        }

        $today = ($asOf ?? Carbon::today())->copy()->startOfDay();

        return Carbon::parse($arrivalDate)->startOfDay()->lte($today);
    }

    /**
     * Remove Coming Soon from cars whose arrival date has passed and stamp created_at.
     */
    public static function expireDueCars(?Carbon $asOf = null): int
    {
        $today = ($asOf ?? Carbon::today())->toDateString();

        $cars = Car::query()
            ->where('is_coming_soon', 'set')
            ->whereNotNull('arrival_date')
            ->whereDate('arrival_date', '<=', $today)
            ->get(['car_id', 'arrival_date']);

        foreach ($cars as $car) {
            $arrivedAt = Carbon::parse($car->arrival_date)->startOfDay()->toDateTimeString();

            DB::table('cars')->where('car_id', $car->car_id)->update([
                'is_coming_soon' => null,
                'arrival_date'   => null,
                'created_at'     => $arrivedAt,
            ]);
        }

        return $cars->count();
    }

    /**
     * Normalize coming-soon fields pulled from the live API before saving locally.
     *
     * @return array{is_coming_soon: ?string, arrival_date: ?string, created_at: ?Carbon}
     */
    public static function normalizeSyncedCar(array $item): array
    {
        $isComingSoon = self::normalizeComingSoonFlag($item['is_coming_soon'] ?? null);
        $arrivalDate = $item['arrival_date'] ?? null;

        if (self::isExpired($isComingSoon, $arrivalDate)) {
            return [
                'is_coming_soon' => null,
                'arrival_date'   => null,
                'created_at'     => Carbon::parse($arrivalDate)->startOfDay(),
            ];
        }

        return [
            'is_coming_soon' => $isComingSoon,
            'arrival_date'   => $arrivalDate,
            'created_at'     => null,
        ];
    }

    private static function normalizeComingSoonFlag(mixed $value): ?string
    {
        if ($value === 'set' || $value === true || $value === 'true' || $value === '1' || $value === 1) {
            return 'set';
        }

        return null;
    }
}
