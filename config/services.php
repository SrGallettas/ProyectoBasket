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

    'apertium' => [
        'base_url' => env('APERTIUM_BASE_URL'),
        'langpair' => env('APERTIUM_LANGPAIR', 'es|ca_valencia'),
        'timeout' => (int) env('APERTIUM_TIMEOUT', 10),
        'protected_terms' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('APERTIUM_PROTECTED_TERMS', 'CB Bellreguard,Bellreguard Basket,Bellreguard CB,Pabellón Municipal de Bellreguard,Pavelló Municipal de Bellreguard,Pista Central'))
        ))),
    ],

    'merchandising' => [
        'orders_email' => env('MERCH_PEDIDOS_EMAIL', env('MERCH_INTERES_EMAIL', env('MAIL_FROM_ADDRESS'))),
    ],

];
