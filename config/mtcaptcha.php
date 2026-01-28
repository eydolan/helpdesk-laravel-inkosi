<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MTCaptcha Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for MTCaptcha integration
    | Note: These settings are managed through AccountSettings
    | This config file is for reference and can be used for direct API calls
    |
    */

    'site_key' => env('MTCAPTCHA_SITE_KEY', ''),
    
    'private_key' => env('MTCAPTCHA_PRIVATE_KEY', ''),
    
    'api_url' => env('MTCAPTCHA_API_URL', 'https://service.mtcaptcha.com/mtcv1/api/checktoken'),
    
    'enabled' => env('MTCAPTCHA_ENABLED', false),
];
