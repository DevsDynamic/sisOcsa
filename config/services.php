<?php

return [

    'osinergmin' => [
        'environment' => in_array(
            env('OSINERGMIN_ENVIRONMENT', env('APP_ENV')),
            ['production', 'prod'],
            true
        ) ? 'production' : 'development',
        'tokens' => [
            'production' => env('OSINERGMIN_TOKEN_PROD'),
            'development' => env('OSINERGMIN_TOKEN_DEV'),
        ],
        'base_urls' => [
            'production' => env(
                'OSINERGMIN_API_BASE_URL_PROD',
                env('OSINERGMIN_API_BASE_URL', 'https://pmgo.osinergmin.gob.pe')
            ),
            'development' => env(
                'OSINERGMIN_API_BASE_URL_DEV',
                env('OSINERGMIN_API_BASE_URL', 'https://srvcertpmgo.osinergmin.gob.pe')
            ),
        ],
        'paths' => [
            'unit' => '/api-gps-ingesta/api/v1/trama',
            'batch' => '/api-gps-ingesta-batch/api/v1/trama-batch',
        ],
    ],

    'ocsa' => [
        'base_url' => env('OCSA_API_BASE_URL', 'https://monitoreo.ocsaperu.com'),
        'paths' => [
            'units' => '/api/v1/unit/list.json',
            'companies' => '/api/v1/company/get.json',
            'alerts' => '/api/v1/alert/list.json',
        ],
    ],

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

];
