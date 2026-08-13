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

    // Shared secret the storefront's backend sends as X-Integration-Key on
    // POST /api/v1/customer/connect - the customer API's only "system to
    // system" trust boundary, since /connect hands out a Sanctum token for
    // any phone number it's given and has no password to check instead.
    'customer_api' => [
        'integration_key' => env('CUSTOMER_API_INTEGRATION_KEY'),
    ],

    // Where the license page's "Buy a license" form (locked/unactivated
    // installs) sends its request. Not env-driven for bank_* - every
    // install of this public codebase should show the SAME owner details,
    // the same way the WhatsApp/email contact chips next to it are
    // hardcoded rather than per-install configurable.
    'license_purchase' => [
        'notify_email' => env('LICENSE_PURCHASE_NOTIFY_EMAIL', 'izmadts@gmail.com'),
        'amount' => 10000,
        'currency' => 'PKR',
        'bank_name' => 'Bank Alfalah',
        'account_title' => 'Mubashar Irshad',
        'account_number' => '55585000106471',
        'iban' => 'PK59ALFH5558005000106471',
        'branch' => '5558',
    ],

    // Remote license-validation server this install activates/checks in
    // against. See App\Services\LicenseService.
    'license_server' => [
        'url' => env('LICENSE_SERVER_URL', 'https://license.wsretail.example.com'),
        'grace_days' => env('LICENSE_GRACE_DAYS', 7),
        'timeout' => env('LICENSE_HTTP_TIMEOUT', 5),
        'recheck_interval_hours' => env('LICENSE_RECHECK_INTERVAL_HOURS', 6),
    ],

];
