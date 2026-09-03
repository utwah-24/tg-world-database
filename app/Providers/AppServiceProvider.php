<?php

namespace App\Providers;

use App\Models\AuthSession;
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
        RateLimiter::for('favorites-list', fn (Request $request) => [
            Limit::perMinute(120)->by('favorites-list-user:'.$this->favoriteActorKey($request)),
            Limit::perMinute(120)->by('favorites-list-ip:'.$request->ip()),
        ]);
        RateLimiter::for('favorites-mutate', fn (Request $request) => [
            Limit::perMinute(60)->by('favorites-mutate-user:'.$this->favoriteActorKey($request)),
            Limit::perMinute(60)->by('favorites-mutate-ip:'.$request->ip()),
        ]);

        Order::observe(OrderObserver::class);
        TestDrive::observe(TestDriveObserver::class);
    }

    private function favoriteActorKey(Request $request): string
    {
        if ($request->user()) {
            return (string) $request->user()->getAuthIdentifier();
        }

        $token = $request->cookie('tgworld_session');
        if (is_string($token) && strlen($token) === 64) {
            $userId = AuthSession::query()
                ->where('token_hash', hash('sha256', $token))
                ->whereNull('revoked_at')
                ->where('expires_at', '>', now())
                ->value('user_id');

            if ($userId) {
                return (string) $userId;
            }
        }

        return 'guest:'.$request->ip();
    }
}
