<?php

namespace App\Observers;

use App\Jobs\SendNewOrderWhatsApp;
use App\Models\Order;

class OrderObserver
{
    public function created(Order $order): void
    {
        SendNewOrderWhatsApp::dispatch($order);
    }
}
