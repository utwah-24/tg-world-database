<?php

namespace App\Console\Commands;

use App\Services\ComingSoonService;
use Illuminate\Console\Command;

class ClearExpiredComingSoon extends Command
{
    protected $signature   = 'cars:clear-coming-soon';

    protected $description = 'Remove Coming Soon status from cars whose arrival date has passed and stamp created_at with the arrival date';

    public function handle(): int
    {
        $count = ComingSoonService::expireDueCars();

        if ($count === 0) {
            $this->info('No cars to update.');

            return self::SUCCESS;
        }

        $this->info("Cleared Coming Soon status from {$count} car(s) and stamped their arrival date as created_at.");

        return self::SUCCESS;
    }
}
