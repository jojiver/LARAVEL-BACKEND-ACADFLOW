<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class AuthCookie
{
    public const NAME = 'accessToken';

    public static function set(JsonResponse $response, string $token): void
    {
        $response->headers->setCookie(cookie(
            self::NAME,
            $token,
            (int) config('auth_cookie.minutes', 60 * 24 * 30),
            '/',
            config('auth_cookie.domain'),
            (bool) config('auth_cookie.secure', false),
            true,
            false,
            (string) config('auth_cookie.same_site', 'lax'),
        ));
    }

    public static function clear(JsonResponse $response): void
    {
        $response->headers->setCookie(cookie()->forget(
            self::NAME,
            '/',
            config('auth_cookie.domain'),
        ));
    }
}
