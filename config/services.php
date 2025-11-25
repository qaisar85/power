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

    // Added Stripe service configuration
    'stripe' => [
        'secret' => env('STRIPE_SECRET'),
        'public' => env('STRIPE_PUBLIC'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    // Added PayPal service configuration
    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret' => env('PAYPAL_SECRET'),
        'mode' => env('PAYPAL_MODE', 'sandbox'), // 'live' or 'sandbox'
        'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
    ],

    // Logistics contact email for shipping requests
    'logistics' => [
        'email' => env('LOGISTICS_EMAIL'),
    ],

    // Twilio credentials for SMS notifications
    'twilio' => [
        'username' => env('TWILIO_USERNAME', env('TWILIO_ACCOUNT_SID')),
        'password' => env('TWILIO_PASSWORD', env('TWILIO_AUTH_TOKEN')),
        'from' => env('TWILIO_FROM'),
        'alphanumeric_sender' => env('TWILIO_ALPHANUMERIC_SENDER'),
    ],

    // OAuth Providers
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL', env('APP_URL').'/auth/google/callback'),
    ],

    'linkedin' => [
        'client_id' => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect' => env('LINKEDIN_REDIRECT_URL', env('APP_URL').'/auth/linkedin/callback'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URL', env('APP_URL').'/auth/facebook/callback'),
    ],
];
