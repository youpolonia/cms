<?php

return [
    'dsn' => $_ENV['SENTRY_LARAVEL_DSN'] ?? null,
    'release' => $_ENV['SENTRY_RELEASE'] ?? null,
    'environment' => $_ENV['SENTRY_ENVIRONMENT'] ?? $_ENV['APP_ENV'] ?? 'production',
    'sample_rate' => isset($_ENV['SENTRY_SAMPLE_RATE']) ? (float)$_ENV['SENTRY_SAMPLE_RATE'] : 1.0,
    'traces_sample_rate' => isset($_ENV['SENTRY_TRACES_SAMPLE_RATE']) ? (float)$_ENV['SENTRY_TRACES_SAMPLE_RATE'] : null,
    'profiles_sample_rate' => isset($_ENV['SENTRY_PROFILES_SAMPLE_RATE']) ? (float)$_ENV['SENTRY_PROFILES_SAMPLE_RATE'] : null,
    'send_default_pii' => $_ENV['SENTRY_SEND_DEFAULT_PII'] ?? false,
    'ignore_transactions' => [
        'health-check',
    ],
];
