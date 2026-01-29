<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Illuminate\Http\RedirectResponse;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    /**
     * Redirect to custom password reset page that supports both email and phone (SMS)
     */
    public function mount(): void
    {
        // Redirect to our custom password reset page that supports email AND phone via SMS gateway
        $this->redirect(route('password.request'));
    }
}
