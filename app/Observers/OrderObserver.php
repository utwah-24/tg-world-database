<?php

namespace App\Observers;

use App\Mail\NewOrderMail;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderObserver
{
    public function created(Order $order): void
    {
        try {
            Mail::to('mwingirautwah@gmail.com')->send(new NewOrderMail($order));
        } catch (\Throwable $e) {
            Log::error('Failed to send new order email: '.$e->getMessage());
        }
    }
}
