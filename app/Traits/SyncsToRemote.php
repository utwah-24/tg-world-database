<?php

namespace App\Traits;

use App\Services\SyncService;

/**
 * Add this trait to any Eloquent model that should be synced to the peer
 * instance (live ↔ local).  Uses Laravel's boot{TraitName} convention so
 * no manual registration is needed.
 *
 * Loop prevention: when an incoming sync payload is being written to the
 * local database, SyncService::$isSyncing is true, so the hook below does
 * NOT push the change back to the originating peer.
 */
trait SyncsToRemote
{
    public static function bootSyncsToRemote(): void
    {
        $push = static function (string $action): \Closure {
            return static function ($model) use ($action): void {
                if (SyncService::$isSyncing) {
                    return;
                }
                SyncService::push(
                    class_basename($model),
                    $action,
                    $model->getAttributes()
                );
            };
        };

        static::created($push('created'));
        static::updated($push('updated'));

        static::deleted(static function ($model): void {
            if (SyncService::$isSyncing) {
                return;
            }
            SyncService::push(
                class_basename($model),
                'deleted',
                [$model->getKeyName() => $model->getKey()]
            );
        });
    }
}
