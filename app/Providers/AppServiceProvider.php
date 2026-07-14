<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\TestDrive;
use App\Observers\OrderObserver;
use App\Observers\TestDriveObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Order::observe(OrderObserver::class);
        TestDrive::observe(TestDriveObserver::class);
    }
}
