<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'enabled' => (bool) env('AUTH_GOOGLE_ENABLED', true), // Default: enabled
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        // Auto-generate redirect URL from APP_URL if not set in ENV
        'redirect' => env('GOOGLE_REDIRECT_URL') ?: (
            rtrim(env('APP_URL', 'http://localhost'), '/') . '/' . 
            env('ADMIN_PREFIX', 'bsadm') . '/oauth/google/callback'
        ),
        'stateless' => (bool) env('GOOGLE_STATELESS', false),
    ],

    'facebook' => [
        'enabled' => (bool) env('AUTH_FACEBOOK_ENABLED', true), // Default: enabled
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        // Auto-generate redirect URL from APP_URL if not set in ENV
        'redirect' => env('FACEBOOK_REDIRECT_URL') ?: (
            rtrim(env('APP_URL', 'http://localhost'), '/') . '/' . 
            env('ADMIN_PREFIX', 'bsadm') . '/oauth/facebook/callback'
        ),
        'stateless' => (bool) env('FACEBOOK_STATELESS', false),
    ],

];
