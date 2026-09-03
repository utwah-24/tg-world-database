<?php

namespace App\Providers;

use App\Models\AuthSession;
use App\Models\Order;
use App\Models\Quotation;
use App\Models\TestDrive;
use App\Observers\OrderObserver;
use App\Observers\QuotationObserver;
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
            Limit::perMinute(120)->by('favorites-list-user:'.$this->customerActorKey($request)),
            Limit::perMinute(120)->by('favorites-list-ip:'.$request->ip()),
        ]);
        RateLimiter::for('favorites-mutate', fn (Request $request) => [
            Limit::perMinute(60)->by('favorites-mutate-user:'.$this->customerActorKey($request)),
            Limit::perMinute(60)->by('favorites-mutate-ip:'.$request->ip()),
        ]);
        RateLimiter::for('quotations-list', fn (Request $request) => [
            Limit::perMinute(120)->by('quotations-list-user:'.$this->customerActorKey($request)),
            Limit::perMinute(120)->by('quotations-list-ip:'.$request->ip()),
        ]);
        RateLimiter::for('quotations-create', fn (Request $request) => [
            Limit::perMinute(10)->by('quotations-create-user:'.$this->customerActorKey($request)),
            Limit::perMinute(20)->by('quotations-create-ip:'.$request->ip()),
        ]);
        RateLimiter::for('quotations-mutate', fn (Request $request) => [
            Limit::perMinute(30)->by('quotations-mutate-user:'.$this->customerActorKey($request)),
            Limit::perMinute(60)->by('quotations-mutate-ip:'.$request->ip()),
        ]);

        Order::observe(OrderObserver::class);
        Quotation::observe(QuotationObserver::class);
        TestDrive::observe(TestDriveObserver::class);
    }

    private function customerActorKey(Request $request): string
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
