<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AutoSyncFromLive
{
    public function handle(Request $request, Closure $next): Response
    {
        // Trigger on full-page navigations only (not Livewire AJAX polls)
        $isNavigation = ! $request->ajax()
            && ! $request->hasHeader('X-Livewire')
            && $request->isMethodSafe();

        if ($isNavigation && config('services.sync.live_url')) {
            // Fire in the background so the page loads instantly.
            // The local DB is updated while you browse; the next refresh
            // will show the latest data.
            $artisan = PHP_BINARY.' '.base_path('artisan');
            exec("{$artisan} sync:pull > /dev/null 2>&1 &");
        }

        return $next($request);
    }
}
