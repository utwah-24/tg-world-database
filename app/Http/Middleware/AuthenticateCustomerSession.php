<?php

namespace App\Http\Middleware;

use App\Services\AuthSessionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateCustomerSession
{
    public function __construct(private AuthSessionService $sessions) {}

    public function handle(Request $request, Closure $next): Response
    {
        $session = $this->sessions->resolve($request);
        if (! $session) {
            return response()->json(['error' => [
                'code' => 'UNAUTHENTICATED',
                'message' => 'Authentication is required.',
                'fields' => (object) [],
            ]], 401);
        }

        $request->setUserResolver(fn () => $session->user);
        $request->attributes->set('auth_session', $session);

        return $next($request);
    }
}
