<?php

namespace App\Console\Commands;

use App\Services\PromotionService;
use Illuminate\Console\Command;

class SyncPromotionStatus extends Command
{
    protected $signature = 'promotions:sync-status';

    protected $description = 'Deactivate expired promotions and reset linked car promo pricing';

    public function handle(): int
    {
        $count = PromotionService::syncStatuses();

        if ($count === 0) {
            $this->info('No promotion status changes.');

            return self::SUCCESS;
        }

        $this->info("Updated status for {$count} promotion(s) and reset affected cars.");

        return self::SUCCESS;
    }
}
