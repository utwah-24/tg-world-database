<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;

class AutoSyncFromLive
{
    public function handle(Request $request, Closure $next): Response
    {
        // Trigger on full-page navigations only (not Livewire AJAX polls)
        $isNavigation = ! $request->ajax()
            && ! $request->hasHeader('X-Livewire')
            && $request->isMethodSafe();

        if ($isNavigation
            && config('services.sync.pull_enabled')
            && filled(config('services.sync.live_url'))) {
            // Fire in the background so the page loads instantly.
            // The local DB is updated while you browse; the next refresh
            // will show the latest data.
            $process = new Process([PHP_BINARY, base_path('artisan'), 'sync:pull']);
            $process->setTimeout(null);
            $process->disableOutput();
            $process->start();
        }

        return $next($request);
    }
}
