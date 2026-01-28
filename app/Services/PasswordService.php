<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PasswordService
{
    protected WinSMSService $winSMSService;

    public function __construct(WinSMSService $winSMSService)
    {
        $this->winSMSService = $winSMSService;
    }

    /**
     * Generate secure random password
     * 8-12 characters, alphanumeric
     *
     * @return string
     */
    public function generatePassword(): string
    {
        $length = rand(8, 12);
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $password;
    }

    /**
     * Send password to user via email or SMS
     *
     * @param User $user
     * @param string $password
     * @return void
     */
    public function sendPassword(User $user, string $password): void
    {
        if ($user->email) {
            $this->sendPasswordEmail($user, $password);
        } elseif ($user->phone) {
            $this->sendPasswordSMS($user, $password);
        } else {
            Log::warning('Cannot send password: user has no email or phone', ['user_id' => $user->id]);
        }
    }

    /**
     * Send password via email
     *
     * @param User $user
     * @param string $password
     * @return void
     */
    protected function sendPasswordEmail(User $user, string $password): void
    {
        try {
            Mail::send('emails.password', [
                'user' => $user,
                'password' => $password,
            ], function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject('Your Account Password');
            });
        } catch (\Exception $e) {
            Log::error('Failed to send password email', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send password via SMS
     *
     * @param User $user
     * @param string $password
     * @return void
     */
    protected function sendPasswordSMS(User $user, string $password): void
    {
        $message = "Your account password is: {$password}. Please save this password securely.";
        $this->winSMSService->sendSMS($user->phone, $message);
    }

    /**
     * Generate and send password reset code
     *
     * @param User $user
     * @return string The reset code
     */
    public function sendPasswordReset(User $user): string
    {
        // Generate 6-digit code
        $code = str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store reset code in database (will be handled by PasswordResetCode model)
        // This method just generates and sends the code
        
        $message = "Your password reset code is: {$code}. This code expires in 15 minutes.";
        
        if ($user->email) {
            try {
                Mail::send('emails.password-reset', [
                    'user' => $user,
                    'code' => $code,
                ], function ($message) use ($user) {
                    $message->to($user->email, $user->name)
                        ->subject('Password Reset Code');
                });
            } catch (\Exception $e) {
                Log::error('Failed to send password reset email', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        } elseif ($user->phone) {
            $this->winSMSService->sendSMS($user->phone, $message);
        }
        
        return $code;
    }
}
