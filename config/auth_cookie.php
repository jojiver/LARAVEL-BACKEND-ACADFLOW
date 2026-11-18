<?php

$production = env('APP_ENV', 'production') === 'production';

return [
    'minutes' => (int) env('AUTH_COOKIE_MINUTES', 60 * 24 * 30),
    'secure' => filter_var(
        env('AUTH_COOKIE_SECURE', $production),
        FILTER_VALIDATE_BOOL,
    ),
    'same_site' => env('AUTH_COOKIE_SAME_SITE', $production ? 'none' : 'lax'),
    'domain' => env('AUTH_COOKIE_DOMAIN') ?: null,
];
