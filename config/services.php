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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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
    | Shopee Open Platform API v2 Configuration
    |--------------------------------------------------------------------------
    | Official Shopee Open Platform API credentials for hourly data sync.
    | Modes: 'live' (production), 'sandbox' (test-stable), or 'mock' (fallback).
    */
    'shopee' => [
        'partner_id'   => env('SHOPEE_PARTNER_ID'),
        'partner_key'  => env('SHOPEE_PARTNER_KEY'),
        'base_url'     => env('SHOPEE_BASE_URL', 'https://partner.shopeemobile.com'),
        'mode'         => env('SHOPEE_API_MODE', 'mock'),
        'timeout'      => (int) env('SHOPEE_API_TIMEOUT', 15),
    ],

    /*
    |--------------------------------------------------------------------------
    | TikTok Shop Open API (v202309 / v202404) Configuration
    |--------------------------------------------------------------------------
    | Official TikTok Shop Open API credentials for hourly data sync.
    | Modes: 'live' (production), 'sandbox' (sandbox-partner), or 'mock' (fallback).
    */
    'tiktok' => [
        'app_key'      => env('TIKTOK_APP_KEY'),
        'app_secret'   => env('TIKTOK_APP_SECRET'),
        'base_url'     => env('TIKTOK_BASE_URL', 'https://open-api.tiktokglobalshop.com'),
        'mode'         => env('TIKTOK_API_MODE', 'mock'),
        'timeout'      => (int) env('TIKTOK_API_TIMEOUT', 15),
    ],

];
