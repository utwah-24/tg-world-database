<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\TestDrive;
use App\Observers\OrderObserver;
use App\Observers\TestDriveObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        RateLimiter::for('auth-login', fn (Request $request) => [
            Limit::perMinute(10)->by('login-ip:'.$request->ip()),
            Limit::perMinute(5)->by('login-id:'.hash('sha256', mb_strtolower((string) $request->input('usernameOrEmail')))),
        ]);
        RateLimiter::for('auth-register', fn (Request $request) => Limit::perHour(10)->by($request->ip()));
        RateLimiter::for('auth-recovery', fn (Request $request) => [
            Limit::perHour(10)->by('recovery-ip:'.$request->ip()),
            Limit::perHour(3)->by('recovery-id:'.hash('sha256', mb_strtolower((string) ($request->input('email') ?: $request->input('token'))))),
        ]);

        Order::observe(OrderObserver::class);
        TestDrive::observe(TestDriveObserver::class);
    }
}
