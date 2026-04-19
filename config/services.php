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

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google_weather' => [
        'api_key' => env('GOOGLE_WEATHER_API_KEY'),
        'base_url' => env('GOOGLE_WEATHER_BASE_URL', 'https://weather.googleapis.com/v1'),
        'timeout' => (int) env('GOOGLE_WEATHER_TIMEOUT', 15),
        'units' => env('GOOGLE_WEATHER_UNITS', 'METRIC'),
        'cache_ttl' => (int) env('GOOGLE_WEATHER_CACHE_TTL', 900),
    ],

    'ml_api' => [
        'url' => env('ML_API_URL', 'http://127.0.0.1:5000'),
        'timeout' => (int) env('ML_API_TIMEOUT', 30),
        'cache_enabled' => (bool) env('ML_API_CACHE_ENABLED', true),
        'cache_ttl' => (int) env('ML_API_CACHE_TTL', 300),
        'strict_mode' => (bool) env('ML_STRICT_MODE', true),
    ],

];
