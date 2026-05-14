<?php

namespace App\Console\Commands;

use App\Models\Car;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ClearExpiredComingSoon extends Command
{
    protected $signature   = 'cars:clear-coming-soon';
    protected $description = 'Remove Coming Soon status from cars whose arrival date has passed and stamp created_at with the arrival date';

    public function handle(): void
    {
        $cars = Car::where('is_coming_soon', 'set')
            ->whereNotNull('arrival_date')
            ->whereDate('arrival_date', '<=', Carbon::today())
            ->get(['car_id', 'arrival_date']);

        if ($cars->isEmpty()) {
            $this->info('No cars to update.');

            return;
        }

        foreach ($cars as $car) {
            $arrivedAt = Carbon::parse($car->arrival_date)->startOfDay()->toDateTimeString();

            // Use DB directly so Eloquent timestamps don't overwrite created_at
            DB::table('cars')->where('car_id', $car->car_id)->update([
                'is_coming_soon' => null,
                'arrival_date'   => null,
                'created_at'     => $arrivedAt,
            ]);
        }

        $this->info("Cleared Coming Soon status from {$cars->count()} car(s) and stamped their arrival date as created_at.");
    }
}
