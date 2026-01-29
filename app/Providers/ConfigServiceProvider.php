<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configGeneral();
        $this->configAccount();
        $this->configMail();
    }

    protected function configGeneral()
    {
        $generalSettings = app(\App\Settings\GeneralSettings::class);

        if ($generalSettings->site_title) {
            config(['app.name' => $generalSettings->site_title]);
        }

        if ($generalSettings->site_url) {
            config(['app.url' => $generalSettings->site_url]);
        }

        if ($generalSettings->site_timezone) {
            config(['app.timezone' => $generalSettings->site_timezone]);
        }

        if ($generalSettings->site_locale) {
            config(['app.locale' => $generalSettings->site_locale]);
        }
    }

    protected function configAccount()
    {
        $accountSettings = app(\App\Settings\AccountSettings::class);

        if ($accountSettings->auth_google_enabled) {
            config(['services.google' => [
                'client_id' => $accountSettings->auth_google_client_id,
                'client_secret' => $accountSettings->auth_google_client_secret,
                'redirect' => url('admin/oauth/callback/google'),
            ]]);
        }

        if ($accountSettings->auth_oauth0_enabled) {
            config(['services.auth0' => [
                'client_id' => $accountSettings->auth_oauth0_client_id,
                'client_secret' => $accountSettings->auth_oauth0_client_secret,
                'redirect' => url('admin/oauth/callback/oauth0'),
                'base_url' => $accountSettings->auth_oauth0_base_url,
            ]]);
        }

        if ($accountSettings->auth_laravelpassport_enabled) {
            config(['services.laravelpassport' => [
                'client_id' => $accountSettings->auth_laravelpassport_client_id,
                'client_secret' => $accountSettings->auth_laravelpassport_client_secret,
                'redirect' => url('admin/oauth/callback/laravelpassport'),
                'host' => $accountSettings->auth_laravelpassport_host,
                'authorize_uri' => $accountSettings->auth_laravelpassport_authorize_uri ?? 'oauth/authorize',
                'token_uri' => $accountSettings->auth_laravelpassport_token_uri ?? 'oauth/token',
                'userinfo_uri' => $accountSettings->auth_laravelpassport_userinfo_uri ?? 'api/user',
            ]]);
        }
    }

    protected function configMail()
    {
        $mailSettings = app(\App\Settings\MailSettings::class);

        if ($mailSettings->from_address) {
            config(['mail.from.address' => $mailSettings->from_address]);
        }

        if ($mailSettings->from_name) {
            config(['mail.from.name' => $mailSettings->from_name]);
        }

        if ($mailSettings->mailer) {
            config(['mail.default' => $mailSettings->mailer]);

            if ($mailSettings->mailer == 'smtp') {
                // Determine encryption based on port (465 = ssl, 587 = tls, others = null)
                $encryption = null;
                if ($mailSettings->smtp_port == 465) {
                    $encryption = 'ssl';
                } elseif ($mailSettings->smtp_port == 587) {
                    $encryption = 'tls';
                }
                
                config(['mail.mailers.smtp' => [
                    'transport' => 'smtp',
                    'host' => $mailSettings->smtp_host,
                    'port' => $mailSettings->smtp_port,
                    'encryption' => $encryption,
                    'username' => $mailSettings->smtp_username ?? '',
                    'password' => $mailSettings->smtp_password ?? '',
                    'timeout' => null,
                    'local_domain' => $mailSettings->smtp_localdomain ?? parse_url((string) config('app.url', 'http://localhost'), PHP_URL_HOST),
                ]]);
            }

            if ($mailSettings->mailer == 'smtp') {
                config(['mail.sendmail' => [
                    'transport' => 'sendmail',
                    'path' => $mailSettings->sendmail_path ?? env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
                ]]);
            }
        }

    }
}
