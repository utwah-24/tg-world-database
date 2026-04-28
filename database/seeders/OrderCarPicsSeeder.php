<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderCarPicsSeeder extends Seeder
{
    public function run(): void
    {
        Order::whereNull('car_pics')
            ->orWhere('car_pics', '[]')
            ->get()
            ->each(function (Order $order): void {
                $car = Car::where('car_name', $order->car_name)->first();

                if ($car && ! empty($car->car_pic)) {
                    $order->car_pics = $car->car_pic;
                    $order->saveQuietly();
                }
            });
    }
}
