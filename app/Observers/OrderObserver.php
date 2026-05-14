<?php

namespace App\Observers;

use App\Mail\NewOrderMail;
use App\Models\Car;
use App\Models\Order;
use App\Services\SyncService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderObserver
{
    public function created(Order $order): void
    {
        // Skip email and stock deduction during sync pulls
        if (SyncService::$isSyncing) {
            return;
        }

        $this->deductStock($order);

        try {
            Mail::to('sharifissaceo@gmail.com')->send(new NewOrderMail($order));
        } catch (\Throwable $e) {
            Log::error('Failed to send new order email: '.$e->getMessage());
        }
    }

    public function deleted(Order $order): void
    {
        if (SyncService::$isSyncing) {
            return;
        }

        $this->restoreStock($order);
    }

    /**
     * Decrement cars.total_available by 1 and store the new remaining count
     * as a snapshot on the order row itself.
     */
    private function deductStock(Order $order): void
    {
        $carId = $order->car_id;

        $car = $carId
            ? Car::find($carId)
            : Car::where('car_name', $order->car_name)->first();

        if (! $car || $car->total_available === null) {
            return;
        }

        $qty = max(1, (int) ($order->qty ?? 1));
        $newAvailable = max(0, $car->total_available - $qty);
        $car->total_available = $newAvailable;
        $car->save();

        // Update the snapshot on the order without triggering observer loop
        Order::withoutEvents(function () use ($order, $newAvailable) {
            $order->total_available = $newAvailable;
            $order->save();
        });
    }

    /**
     * Restore 1 unit to cars.total_available when an order is deleted.
     */
    private function restoreStock(Order $order): void
    {
        if ($order->total_available === null) {
            return;
        }

        $carId = $order->car_id;

        $car = $carId
            ? Car::find($carId)
            : Car::where('car_name', $order->car_name)->first();

        if (! $car || $car->total_available === null) {
            return;
        }

        $qty = max(1, (int) ($order->qty ?? 1));
        $car->total_available = $car->total_available + $qty;
        $car->save();
    }
}
