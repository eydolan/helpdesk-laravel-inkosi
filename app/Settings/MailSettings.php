<?php

namespace App\Settings;

class MailSettings extends Settings
{
    public string $mailer;

    public ?string $smtp_scheme = 'smtp';

    public string $smtp_host;

    public int $smtp_port;

    public ?string $smtp_username = '';

    public ?string $smtp_password = '';

    public string $smtp_localdomain;

    public string $sendmail_path;

    public string $from_address;

    public string $from_name;

    public static function group(): string
    {
        return 'mail';
    }
}
