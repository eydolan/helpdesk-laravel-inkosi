<?php

namespace App\Filament\Pages\Setting;

use App\Models\Setting;
use App\Settings\AccountSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Support\Facades\Gate;

class Account extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = AccountSettings::class;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Account');
    }

    public function getTitle(): string
    {
        return self::getNavigationLabel();
    }

    public static function canAccess(): bool
    {
        return Gate::check('viewAny', Setting::class);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Account'))
                    ->schema([
                        Forms\Components\Toggle::make('user_registration')
                            ->label(__('Allow User Registration'))
                            ->helperText(__('Allow users to create accounts on the login screen'))
                            ->required(),

                        Forms\Components\Toggle::make('user_email_verification')
                            ->label(__('Email Verification'))
                            ->helperText(__('Require users to validate their email to login'))
                            ->required(),

                        Forms\Components\Toggle::make('user_password_reset')
                            ->label(__('Password Reset'))
                            ->helperText(__('Allow users to recover their password'))
                            ->required(),
                    ]),

                Forms\Components\Section::make(__('Auth with Google'))
                    ->schema([
                        Forms\Components\Toggle::make('auth_google_enabled')
                            ->label(__('Enabled'))
                            ->live()
                            ->helperText(__('Allow users to sign in with Google account'))
                            ->required(),

                        Forms\Components\Toggle::make('auth_google_registration')
                            ->label(__('Allow Registration'))
                            ->helperText(__('Allow users to be registered when logging in with their Google account'))
                            ->visible(fn ($get) => $get('auth_google_enabled'))
                            ->required(),

                        Forms\Components\Toggle::make('auth_google_stateless')
                            ->label(__('Stateless'))
                            ->visible(fn ($get) => $get('auth_google_enabled'))
                            ->required(),

                        Forms\Components\TextInput::make('auth_google_client_id')
                            ->label(__('Client ID'))
                            ->visible(fn ($get) => $get('auth_google_enabled'))
                            ->required(fn ($get) => $get('auth_google_enabled')),

                        Forms\Components\TextInput::make('auth_google_client_secret')
                            ->label(__('Client Secret'))
                            ->visible(fn ($get) => $get('auth_google_enabled'))
                            ->required(fn ($get) => $get('auth_google_enabled')),

                        Forms\Components\Placeholder::make('auth_google_redirect')
                            ->label(__('Redirect'))
                            ->content(url('admin/oauth/callback/google'))
                            ->visible(fn ($get) => $get('auth_google_enabled')),

                        Forms\Components\TagsInput::make('auth_google_scopes')
                            ->label(__('Scopes'))
                            ->placeholder(__('Scopes'))
                            ->visible(fn ($get) => $get('auth_google_enabled')),
                    ]),

                Forms\Components\Section::make(__('Auth with OAuth0'))
                    ->schema([
                        Forms\Components\Toggle::make('auth_oauth0_enabled')
                            ->label(__('Enabled'))
                            ->live()
                            ->helperText(__('Allow users to sign in with OAuth0'))
                            ->required(),

                        Forms\Components\Toggle::make('auth_oauth0_registration')
                            ->label(__('Allow Registration'))
                            ->helperText('Allow users to be registered when logging in with OAuth0 account')
                            ->visible(fn ($get) => $get('auth_oauth0_enabled'))
                            ->required(),

                        Forms\Components\Toggle::make('auth_oauth0_stateless')
                            ->label(__('Stateless'))
                            ->visible(fn ($get) => $get('auth_oauth0_enabled'))
                            ->required(),

                        Forms\Components\TextInput::make('auth_oauth0_title')
                            ->label(__('Button Title'))
                            ->visible(fn ($get) => $get('auth_oauth0_enabled'))
                            ->required(fn ($get) => $get('auth_oauth0_enabled')),

                        Forms\Components\ColorPicker::make('auth_oauth0_color')
                            ->label(__('Button Color'))
                            ->visible(fn ($get) => $get('auth_oauth0_enabled'))
                            ->required(fn ($get) => $get('auth_oauth0_enabled')),

                        Forms\Components\TextInput::make('auth_oauth0_client_id')
                            ->label(__('Client ID'))
                            ->visible(fn ($get) => $get('auth_oauth0_enabled'))
                            ->required(fn ($get) => $get('auth_oauth0_enabled')),

                        Forms\Components\TextInput::make('auth_oauth0_client_secret')
                            ->label(__('Client Secret'))
                            ->visible(fn ($get) => $get('auth_oauth0_enabled'))
                            ->required(fn ($get) => $get('auth_oauth0_enabled')),

                        Forms\Components\TextInput::make('auth_oauth0_base_url')
                            ->label(__('Base URL'))
                            ->visible(fn ($get) => $get('auth_oauth0_enabled'))
                            ->required(fn ($get) => $get('auth_oauth0_enabled')),

                        Forms\Components\Placeholder::make('auth_oauth0_redirect')
                            ->label(__('Redirect'))
                            ->content(url('admin/oauth/callback/oauth0'))
                            ->visible(fn ($get) => $get('auth_oauth0_enabled')),

                        Forms\Components\TagsInput::make('auth_oauth0_scopes')
                            ->label(__('Scopes'))
                            ->placeholder(__('Scopes'))
                            ->visible(fn ($get) => $get('auth_oauth0_enabled')),

                        Forms\Components\KeyValue::make('auth_oauth0_extra_parameters')
                            ->label(__('Extra Parameters'))
                            ->visible(fn ($get) => $get('auth_oauth0_enabled')),
                    ]),

                Forms\Components\Section::make(__('Auth with Laravel Passport'))
                    ->schema([
                        Forms\Components\Toggle::make('auth_laravelpassport_enabled')
                            ->label(__('Enabled'))
                            ->live()
                            ->helperText(__('Allow users to sign in with Laravel Passport'))
                            ->required(),

                        Forms\Components\Toggle::make('auth_laravelpassport_registration')
                            ->label(__('Allow Registration'))
                            ->helperText(__('Allow users to be registered when logging in with Laravel Passport account'))
                            ->visible(fn ($get) => $get('auth_laravelpassport_enabled'))
                            ->required(),

                        Forms\Components\Toggle::make('auth_laravelpassport_stateless')
                            ->label(__('Stateless'))
                            ->visible(fn ($get) => $get('auth_laravelpassport_enabled'))
                            ->required(),

                        Forms\Components\TextInput::make('auth_laravelpassport_title')
                            ->label(__('Button Title'))
                            ->visible(fn ($get) => $get('auth_laravelpassport_enabled'))
                            ->required(fn ($get) => $get('auth_laravelpassport_enabled')),

                        Forms\Components\ColorPicker::make('auth_laravelpassport_color')
                            ->label(__('Button Color'))
                            ->visible(fn ($get) => $get('auth_laravelpassport_enabled'))
                            ->required(fn ($get) => $get('auth_laravelpassport_enabled')),

                        Forms\Components\TextInput::make('auth_laravelpassport_client_id')
                            ->label(__('Client ID'))
                            ->visible(fn ($get) => $get('auth_laravelpassport_enabled'))
                            ->required(fn ($get) => $get('auth_laravelpassport_enabled')),

                        Forms\Components\TextInput::make('auth_laravelpassport_client_secret')
                            ->label(__('Client Secret'))
                            ->visible(fn ($get) => $get('auth_laravelpassport_enabled'))
                            ->required(fn ($get) => $get('auth_laravelpassport_enabled')),

                        Forms\Components\TextInput::make('auth_laravelpassport_host')
                            ->label(__('Host'))
                            ->visible(fn ($get) => $get('auth_laravelpassport_enabled'))
                            ->required(fn ($get) => $get('auth_laravelpassport_enabled')),

                        Forms\Components\Placeholder::make('auth_laravelpassport_redirect')
                            ->label(__('Redirect'))
                            ->content(url('admin/oauth/callback/laravelpassport'))
                            ->visible(fn ($get) => $get('auth_laravelpassport_enabled')),

                        Forms\Components\TextInput::make('auth_laravelpassport_authorize_uri')
                            ->label(__('Authorize URI'))
                            ->visible(fn ($get) => $get('auth_laravelpassport_enabled'))
                            ->required(fn ($get) => $get('auth_laravelpassport_enabled')),

                        Forms\Components\TextInput::make('auth_laravelpassport_token_uri')
                            ->label(__('Token URI'))
                            ->visible(fn ($get) => $get('auth_laravelpassport_enabled'))
                            ->required(fn ($get) => $get('auth_laravelpassport_enabled')),

                        Forms\Components\TextInput::make('auth_laravelpassport_userinfo_uri')
                            ->label(__('User Info URI'))
                            ->visible(fn ($get) => $get('auth_laravelpassport_enabled'))
                            ->required(fn ($get) => $get('auth_laravelpassport_enabled')),

                        Forms\Components\TagsInput::make('auth_laravelpassport_scopes')
                            ->label(__('Scopes'))
                            ->placeholder(__('Scopes'))
                            ->visible(fn ($get) => $get('auth_laravelpassport_enabled')),

                        Forms\Components\KeyValue::make('auth_laravelpassport_extra_parameters')
                            ->label(__('Extra Parameters'))
                            ->visible(fn ($get) => $get('auth_laravelpassport_enabled')),
                    ]),

                Forms\Components\Section::make(__('MTCaptcha'))
                    ->schema([
                        Forms\Components\Toggle::make('mtcaptcha_enabled')
                            ->label(__('Enabled'))
                            ->live()
                            ->helperText(__('Enable MTCaptcha on login, registration, and public ticket submission pages. Validation is skipped on localhost for testing.'))
                            ->required(),

                        Forms\Components\TextInput::make('mtcaptcha_site_key')
                            ->label(__('Site Key'))
                            ->helperText(__('MTCaptcha site key'))
                            ->visible(fn ($get) => $get('mtcaptcha_enabled'))
                            ->required(fn ($get) => $get('mtcaptcha_enabled')),

                        Forms\Components\TextInput::make('mtcaptcha_private_key')
                            ->label(__('Private Key'))
                            ->helperText(__('MTCaptcha private key'))
                            ->password()
                            ->visible(fn ($get) => $get('mtcaptcha_enabled'))
                            ->required(fn ($get) => $get('mtcaptcha_enabled')),
                    ]),
            ]);
    }
}
