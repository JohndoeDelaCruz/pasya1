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

    /*
    |--------------------------------------------------------------------------
    | Weather API Services
    |--------------------------------------------------------------------------
    |
    | Configuration for weather API providers including Google Weather API,
    | OpenWeatherMap, and WeatherAPI.com
    |
    */

    'weather' => [
        'provider' => env('WEATHER_API_PROVIDER', 'google'),
        
        'google' => [
            'key' => env('GOOGLE_WEATHER_API_KEY', 'AIzaSyApL1FMpz-YmofnouGJStne7oPv09Ah7jM'),
            'base_url' => 'https://weather.googleapis.com/v1',
        ],
        
        'openweather' => [
            'key' => env('OPENWEATHER_API_KEY'),
            'base_url' => 'https://api.openweathermap.org/data/2.5',
        ],
        
        'weatherapi' => [
            'key' => env('WEATHERAPI_KEY'),
            'base_url' => 'https://api.weatherapi.com/v1',
        ],
    ],

];
