<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Page;

class UpdatePasswordReset extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.auth.update-password-reset';
    
    // Hide from navigation - this page is not functional
    // Password reset is handled by public routes: /password/reset/update
    protected static bool $shouldRegisterNavigation = false;
}
