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

    'asaas' => [
        'sandbox_url' => env('ASAAS_SANDBOX_URL', 'https://api-sandbox.asaas.com'),
        'production_url' => env('ASAAS_PRODUCTION_URL', 'https://api.asaas.com'),
        'user_agent' => env('ASAAS_USER_AGENT', 'ConectadoEmSergipe/1.0'),
        'webhook_base_url' => env('ASAAS_WEBHOOK_BASE_URL') ?: env('APP_URL'),
    ],

    'consultar_crm' => [
        'url' => env('CONSULTAR_CRM_API_URL', 'https://www.consultarcrm.com.br/api/index.php'),
        'key' => env('CONSULTAR_CRM_API_KEY'),
        'timeout' => (int) env('CONSULTAR_CRM_API_TIMEOUT', 12),
    ],

];
