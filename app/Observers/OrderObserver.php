<?php

namespace App\Observers;

use App\Mail\NewOrderMail;
use App\Models\Order;
use App\Services\SyncService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderObserver
{
    public function created(Order $order): void
    {
        // Skip email during sync pulls — only notify for real new orders
        if (SyncService::$isSyncing) {
            return;
        }

        try {
            Mail::to('sharifissaceo@gmail.com')->send(new NewOrderMail($order));
        } catch (\Throwable $e) {
            Log::error('Failed to send new order email: '.$e->getMessage());
        }
    }
}
