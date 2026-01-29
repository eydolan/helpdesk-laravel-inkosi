<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Page;

class VerifyPasswordReset extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.auth.verify-password-reset';
    
    // Hide from navigation - this page is not functional
    // Password reset verification is handled by public route: /password/reset/verify
    protected static bool $shouldRegisterNavigation = false;
}
