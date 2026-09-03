<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyFrontendOrigin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        $origin = $request->headers->get('Origin');
        $allowed = config('cors.allowed_origins', []);

        // Non-browser clients usually send no Origin. Browsers sending cookies always do.
        if ($origin !== null && ! in_array(rtrim($origin, '/'), array_map(fn ($item) => rtrim($item, '/'), $allowed), true)) {
            return response()->json(['error' => [
                'code' => 'INVALID_ORIGIN',
                'message' => 'This request origin is not allowed.',
                'fields' => (object) [],
            ]], 403);
        }

        return $next($request);
    }
}
