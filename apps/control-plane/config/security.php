<?php

return [
    /*
     * Server-side password pepper (secret).
     *
     * - Store this in a secret manager / environment variable.
     * - DO NOT commit it to git.
     * - Rotating this value will invalidate all existing passwords unless you
     *   implement a pepper rotation strategy.
     */
    'password_pepper' => env('AUTH_PASSWORD_PEPPER'),

    /*
     * Previous pepper value for safe rotation.
     *
     * If set, login will accept both current and previous pepper and will
     * transparently re-hash the password with the current pepper on success.
     */
    'password_pepper_previous' => env('AUTH_PASSWORD_PEPPER_PREVIOUS'),

    /*
     * Whether a password pepper is required.
     *
     * Recommended: true for production. Keep false if you want to allow
     * deployments without pepper (not recommended for high-security systems).
     */
    'require_password_pepper' => (bool) env('AUTH_PASSWORD_PEPPER_REQUIRED', false),

    /*
     * Login throttling (email + password buoc 1).
     */
    'login' => [
        'max_attempts' => (int) env('LOGIN_MAX_ATTEMPTS', 5),
        'decay_seconds' => (int) env('LOGIN_DECAY_SECONDS', 60),
    ],

    /*
     * 2FA challenge throttling (TOTP / recovery code step).
     */
    'two_factor' => [
        'challenge_max_attempts' => (int) env('TWO_FACTOR_CHALLENGE_MAX_ATTEMPTS', 5),
        'challenge_decay_seconds' => (int) env('TWO_FACTOR_CHALLENGE_DECAY_SECONDS', 60),
    ],

    /*
     * Audit log retention (days). Use audit:prune to enforce retention.
     */
    'audit' => [
        'retention_days' => (int) env('AUDIT_LOG_RETENTION_DAYS', 90),
    ],
];


