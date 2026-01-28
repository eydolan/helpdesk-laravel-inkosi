<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('mail.mailer', 'log');
        $this->migrator->add('mail.smtp_scheme', env('MAIL_SCHEME', 'smtp'));
        $this->migrator->add('mail.smtp_host', env('MAIL_HOST', '127.0.0.1'));
        $this->migrator->add('mail.smtp_port', env('MAIL_PORT', 2525));
        $this->migrator->add('mail.smtp_username', env('MAIL_USERNAME', ''));
        $this->migrator->add('mail.smtp_password', env('MAIL_PASSWORD', ''));
        $this->migrator->add('mail.smtp_localdomain', env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)));
        $this->migrator->add('mail.from_address', env('MAIL_FROM_ADDRESS', 'hello@example.com'));
        $this->migrator->add('mail.from_name', env('MAIL_FROM_NAME', 'Example'));
        $this->migrator->add('mail.sendmail_path', env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'));
    }
};
