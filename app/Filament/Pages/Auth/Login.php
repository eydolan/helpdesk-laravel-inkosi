<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use App\Services\MTCaptchaService;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected function getForms(): array
    {
        $components = [
            $this->getEmailFormComponent()
                ->label('Email or Phone')
                ->helperText('Enter your email address or phone number'),
            $this->getPasswordFormComponent(),
            $this->getRememberFormComponent(),
        ];

        $mtcaptchaComponent = $this->getMTCaptchaComponent();
        if ($mtcaptchaComponent !== null) {
            $components[] = $mtcaptchaComponent;
        }

        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema($components)
                    ->statePath('data'),
            ),
        ];
    }

    /**
     * Authenticate the user.
     */
    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();
        
        // Validate MTCaptcha if enabled
        $mtcaptchaService = app(MTCaptchaService::class);
        if ($mtcaptchaService->shouldShow()) {
            $token = $data['mtcaptcha-verifiedtoken'] ?? null;
            if (!$mtcaptchaService->validateToken($token)) {
                throw ValidationException::withMessages([
                    'data.mtcaptcha-verifiedtoken' => __('The MTCaptcha verification failed. Please try again.'),
                ]);
            }
        }
        
        return parent::authenticate();
    }

    /**
     * Get the credentials for authentication.
     * Support both email and phone number login.
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        $login = $data['email'] ?? null;
        
        if (!$login) {
            return [];
        }

        // Determine if login is email or phone
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            // It's an email - use standard email field
            return [
                'email' => $login,
                'password' => $data['password'],
            ];
        } else {
            // It's a phone number - use 'email' field but store phone for lookup
            // We'll handle phone lookup in a custom UserProvider
            return [
                'email' => $login, // Store phone in email field temporarily
                'phone' => $login, // Also include phone for provider lookup
                'password' => $data['password'],
            ];
        }
    }

    protected function getMTCaptchaComponent(): ?Component
    {
        $mtcaptchaService = app(MTCaptchaService::class);

        if (!$mtcaptchaService->shouldShow()) {
            return null;
        }

        return View::make('filament.pages.auth.mtcaptcha-widget')
            ->viewData([
                'siteKey' => $mtcaptchaService->getSiteKey(),
            ]);
    }

}
