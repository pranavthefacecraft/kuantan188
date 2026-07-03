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

    'google' => [
        'places_api_key' => env('GOOGLE_PLACES_API_KEY'),
        'place_id' => env('GOOGLE_PLACE_ID'),
    ],

    'billplz' => [
        'api_key' => env('BILLPLZ_API_KEY'),
        'collection_id' => env('BILLPLZ_COLLECTION_ID'),
        'x_signature_key' => env('BILLPLZ_X_SIGNATURE_KEY'),
        'secret_key' => env('BILLPLZ_SECRET_KEY'),
        'sandbox_url' => env('BILLPLZ_SANDBOX_URL', 'https://www.billplz-sandbox.com/api/v3'),
        'production_url' => env('BILLPLZ_PRODUCTION_URL', 'https://www.billplz.com/api/v3'),
        'environment' => env('BILLPLZ_ENVIRONMENT', 'sandbox'),
        'callback_url' => env('BILLPLZ_CALLBACK_URL'),
        'redirect_url' => env('BILLPLZ_REDIRECT_URL'),
    ],

    'brevo' => [
        'api_key' => env('BREVO_API_KEY'),
    ],

    'admin' => [
        'notification_emails' => env('ADMIN_NOTIFICATION_EMAILS', 'admin@kuantan188.com,yusri@thefacecraft.com'),
    ],

];
