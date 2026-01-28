<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use App\Services\MTCaptchaService;
use App\Services\PasswordService;
use App\Services\WinSMSService;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class Register extends BaseRegister
{
    protected PasswordService $passwordService;
    protected WinSMSService $winSMSService;

    public function mount(): void
    {
        parent::mount();
        $this->passwordService = app(PasswordService::class);
        $this->winSMSService = app(WinSMSService::class);
    }

    protected function getForms(): array
    {
        $components = [
            $this->getNameFormComponent(),
            $this->getEmailFormComponent(),
            $this->getPhoneFormComponent(),
            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),
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

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::pages/auth/register.form.email.label'))
            ->email()
            ->required(false)
            ->maxLength(255)
            ->unique($this->getUserModel(), ignoreRecord: true)
            ->helperText('Either email or phone number is required');
    }

    protected function getPhoneFormComponent(): Component
    {
        return TextInput::make('phone')
            ->label('Phone Number')
            ->tel()
            ->helperText('Either email or phone number is required')
            ->maxLength(255)
            ->required(false)
            ->unique(User::class, 'phone', ignoreRecord: true);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::pages/auth/register.form.password.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required(fn ($get) => empty($get('phone')))
            ->helperText(fn ($get) => empty($get('phone')) ? 'Required if no phone number provided' : 'Optional if phone number provided - password will be sent via SMS')
            ->rule(fn ($get) => empty($get('phone')) ? Password::default() : 'nullable')
            ->dehydrated(fn ($get) => empty($get('phone'))) // Don't dehydrate if phone is provided (we'll generate password)
            ->dehydrateStateUsing(fn ($state, $get) => empty($get('phone')) ? Hash::make($state) : null)
            ->same('passwordConfirmation')
            ->validationAttribute(__('filament-panels::pages/auth/register.form.password.validation_attribute'));
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('filament-panels::pages/auth/register.form.password_confirmation.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required(fn ($get) => empty($get('phone')))
            ->dehydrated(false);
    }

    /**
     * Register the user.
     */
    public function register(): ?RegistrationResponse
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
        
        return parent::register();
    }

    /**
     * Mutate form data before registration
     */
    protected function mutateFormDataBeforeRegister(array $data): array
    {
        // Validate that at least email or phone is provided
        if (empty($data['email']) && empty($data['phone'])) {
            throw ValidationException::withMessages([
                'data.email' => __('Either email or phone number is required.'),
                'data.phone' => __('Either email or phone number is required.'),
            ]);
        }

        return $data;
    }

    /**
     * Handle user registration with phone number support
     */
    protected function handleRegistration(array $data): Model
    {
        $phone = $data['phone'] ?? null;
        $hasPhone = !empty($phone);

        // If phone is provided, generate password and send via SMS
        if ($hasPhone) {
            $password = $this->passwordService->generatePassword();
            $data['password'] = Hash::make($password);
        }
        // If no phone, password is already hashed by Filament's default dehydrateStateUsing

        // Create user
        $user = $this->getUserModel()::create($data);

        // Send password via SMS if phone provided
        if ($hasPhone) {
            $this->passwordService->sendPassword($user, $password);
        }

        return $user;
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
