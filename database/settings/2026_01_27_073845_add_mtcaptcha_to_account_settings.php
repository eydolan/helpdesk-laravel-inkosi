<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('account.mtcaptcha_enabled', false);
        $this->migrator->add('account.mtcaptcha_site_key', 'MTPublic-xjuhrIxme');
        $this->migrator->add('account.mtcaptcha_private_key', 'MTPrivat-xjuhrIxme-WO9nthpEif21agfgmW70Tv7A2aWPnuZdMhX1pp29uR6AZTPA50');
    }
};
