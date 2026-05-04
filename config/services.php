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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => env('MAILGUN_SCHEME', 'https'),
    ],

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

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'plans' => [
            'starter' => [
                'product_id' => env('STRIPE_STARTER_PRODUCT_ID'),
                'monthly_price_id' => env('STRIPE_STARTER_MONTHLY_PRICE_ID'),
                'yearly_price_id' => env('STRIPE_STARTER_YEARLY_PRICE_ID'),
            ],
            'pro' => [
                'product_id' => env('STRIPE_PRO_PRODUCT_ID'),
                'monthly_price_id' => env('STRIPE_PRO_MONTHLY_PRICE_ID'),
                'yearly_price_id' => env('STRIPE_PRO_YEARLY_PRICE_ID'),
            ],
        ],
    ],

];
