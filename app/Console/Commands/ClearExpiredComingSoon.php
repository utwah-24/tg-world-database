<?php

namespace App\Console\Commands;

use App\Models\Car;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ClearExpiredComingSoon extends Command
{
    protected $signature   = 'cars:clear-coming-soon';
    protected $description = 'Remove Coming Soon status from cars whose arrival date has passed';

    public function handle(): void
    {
        $count = Car::where('is_coming_soon', true)
            ->whereNotNull('arrival_date')
            ->whereDate('arrival_date', '<=', Carbon::today())
            ->update([
                'is_coming_soon' => false,
                'arrival_date'   => null,
            ]);

        $this->info("Cleared Coming Soon status from {$count} car(s).");
    }
}
