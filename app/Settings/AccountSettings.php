<?php

namespace App\Settings;

class AccountSettings extends Settings
{
    public bool $user_registration;

    public bool $user_email_verification;

    public bool $user_password_reset;

    public bool $auth_google_enabled;

    public bool $auth_google_registration;

    public array $auth_google_scopes;

    public bool $auth_google_stateless;

    public string $auth_google_client_id;

    public string $auth_google_client_secret;

    public bool $auth_oauth0_enabled;

    public bool $auth_oauth0_registration;

    public string $auth_oauth0_title;

    public string $auth_oauth0_color;

    public array $auth_oauth0_scopes;

    public array $auth_oauth0_extra_parameters;

    public bool $auth_oauth0_stateless;

    public string $auth_oauth0_client_id;

    public string $auth_oauth0_client_secret;

    public string $auth_oauth0_base_url;

    public bool $auth_laravelpassport_enabled;

    public bool $auth_laravelpassport_registration;

    public string $auth_laravelpassport_title;

    public string $auth_laravelpassport_color;

    public array $auth_laravelpassport_scopes;

    public array $auth_laravelpassport_extra_parameters;

    public bool $auth_laravelpassport_stateless;

    public string $auth_laravelpassport_client_id;

    public string $auth_laravelpassport_client_secret;

    public string $auth_laravelpassport_host;

    public string $auth_laravelpassport_authorize_uri;

    public string $auth_laravelpassport_token_uri;

    public string $auth_laravelpassport_userinfo_uri;

    public bool $mtcaptcha_enabled = false;

    public string $mtcaptcha_site_key = 'MTPublic-xjuhrIxme';

    public string $mtcaptcha_private_key = 'MTPrivat-xjuhrIxme-WO9nthpEif21agfgmW70Tv7A2aWPnuZdMhX1pp29uR6AZTPA50';

    public static function group(): string
    {
        return 'account';
    }
}
