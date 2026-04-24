<?php

namespace App\Console\Commands;

use App\Services\LiveSyncService;
use Illuminate\Console\Command;

class SyncPullCommand extends Command
{
    protected $signature = 'sync:pull';

    protected $description = 'Fetch all data from the live site API and save it to the local database.';

    public function handle(LiveSyncService $sync): int
    {
        $base = config('services.sync.live_url');

        if (! $base) {
            $this->error('Set LIVE_APP_URL in your .env');

            return self::FAILURE;
        }

        $this->info("Pulling from: {$base}");

        $ok = $sync->pull();

        if ($ok) {
            $this->info('Done — open http://127.0.0.1:8000/admin to see the data.');

            return self::SUCCESS;
        }

        $this->error('Sync failed — check the log for details.');

        return self::FAILURE;
    }
}
