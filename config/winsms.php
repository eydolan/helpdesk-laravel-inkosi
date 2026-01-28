<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WinSMS API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for WinSMS API integration
    |
    */

    'api_key' => env('WINSMS_API_KEY', ''),
    
    'username' => env('WINSMS_USERNAME'),
    
    'sender_id' => env('WINSMS_SENDER_ID'),
    
    'api_url' => env('WINSMS_API_URL', 'https://www.winsms.co.za/api/batchmessage.asp'),
    
    /*
    |--------------------------------------------------------------------------
    | SMS Message Templates
    |--------------------------------------------------------------------------
    |
    | Templates for various SMS messages
    |
    */
    
    'templates' => [
        'password' => 'Your account password is: :password. Please save this password securely.',
        'password_reset' => 'Your password reset code is: :code. This code expires in 15 minutes.',
    ],
];
