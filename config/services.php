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
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_CLIENT_REDIRECT'),
    ],

    'auth0' => [
        'client_id' => env('AUTH0_CLIENT_ID', 28),
        'client_secret' => env('AUTH0_CLIENT_SECRET'),
        'redirect' => env('AUTH0_REDIRECT_URI'),
        'base_url' => env('AUTH0_BASE_URL'),
    ],

    'laravelpassport' => [
        'client_id' => env('LARAVELPASSPORT_CLIENT_ID', 28),
        'client_secret' => env('LARAVELPASSPORT_CLIENT_SECRET', ''),
        'redirect' => env('LARAVELPASSPORT_REDIRECT_URI', 'http://localhost:8080/admin/oauth/callback/laravelpassport'),
        'host' => env('LARAVELPASSPORT_HOST', 'http://localhost:8101'),
        'userinfo_uri' => 'api/user/me',
        // 'authorize_uri' => 'oauth/authorize',
        // 'token_uri'     => 'oauth/token',
        // 'userinfo_uri'  => 'api/user',
    ],

];
