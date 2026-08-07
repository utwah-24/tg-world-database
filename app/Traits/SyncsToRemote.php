<?php

namespace App\Traits;

/**
 * Push model changes to a peer instance (local ↔ live sync).
 *
 * Disabled on production when SYNC_PEER_URL / SYNC_SECRET are unset.
 * Loop prevention: SyncService::$isSyncing is true while ingesting a pull payload.
 */
trait SyncsToRemote
{
    public static function bootSyncsToRemote(): void
    {
        if (! config('services.sync.push_enabled')) {
            return;
        }

        $push = static function (string $action): \Closure {
            return static function ($model) use ($action): void {
                if (\App\Services\SyncService::$isSyncing) {
                    return;
                }

                \App\Services\SyncService::push(
                    class_basename($model),
                    $action,
                    $model->getAttributes()
                );
            };
        };

        static::created($push('created'));
        static::updated($push('updated'));

        static::deleted(static function ($model): void {
            if (\App\Services\SyncService::$isSyncing) {
                return;
            }

            \App\Services\SyncService::push(
                class_basename($model),
                'deleted',
                [$model->getKeyName() => $model->getKey()]
            );
        });
    }
}
