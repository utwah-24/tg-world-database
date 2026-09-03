<?php

namespace App\Services;

use App\Models\AuthSession;
use App\Models\Client;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class AuthSessionService
{
    public const COOKIE = 'tgworld_session';

    public function create(Client $user, Request $request): array
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = now()->addMinutes((int) config('auth.customer_session_lifetime', 10080));

        AuthSession::create([
            'token_hash' => hash('sha256', $token),
            'user_id' => $user->getKey(),
            'expires_at' => $expiresAt,
            'last_used_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
        ]);

        return [$token, $expiresAt];
    }

    public function resolve(Request $request): ?AuthSession
    {
        $token = $request->cookie(self::COOKIE);
        if (! is_string($token) || strlen($token) !== 64) {
            return null;
        }

        $session = AuthSession::with('user')
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $session || ! $session->user || $session->user->disabled_at) {
            return null;
        }

        if (! $session->last_used_at || $session->last_used_at->lt(now()->subMinutes(5))) {
            $session->forceFill(['last_used_at' => now()])->save();
        }

        return $session;
    }

    public function cookie(string $token, \DateTimeInterface $expiresAt): Cookie
    {
        return cookie(
            self::COOKIE,
            $token,
            max(1, now()->diffInMinutes($expiresAt)),
            '/',
            config('session.domain'),
            (bool) config('session.secure'),
            true,
            false,
            config('session.same_site', 'lax'),
        );
    }

    public function forgetCookie(): Cookie
    {
        return cookie()->forget(self::COOKIE, '/', config('session.domain'));
    }
}
