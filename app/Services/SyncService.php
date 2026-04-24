<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncService
{
    /**
     * Set to true while processing an incoming sync payload so model observers
     * skip re-pushing the same change back to the peer (loop prevention).
     */
    public static bool $isSyncing = false;

    /**
     * Push a model change to the configured peer instance.
     *
     * @param  string  $model   Short class name, e.g. "Car", "Order"
     * @param  string  $action  "created" | "updated" | "deleted"
     * @param  array   $data    Model attributes (or just the PK for deletes)
     */
    public static function push(string $model, string $action, array $data): void
    {
        $peerUrl = config('services.sync.peer_url');
        $secret  = config('services.sync.secret');

        if (! $peerUrl || ! $secret) {
            return;
        }

        try {
            $response = Http::withHeaders([
                'X-Sync-Secret' => $secret,
                'Accept'        => 'application/json',
            ])
            ->timeout(5)
            ->post(rtrim($peerUrl, '/').'/api/sync/ingest', [
                'model'  => $model,
                'action' => $action,
                'data'   => $data,
            ]);

            if (! $response->successful()) {
                Log::warning('Sync push non-2xx response', [
                    'model'  => $model,
                    'action' => $action,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Sync push failed: '.$e->getMessage(), [
                'model'  => $model,
                'action' => $action,
            ]);
        }
    }
}
