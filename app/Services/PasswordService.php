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
            // Force SMTP mailer to ensure emails are actually sent
            Mail::mailer('smtp')->send('emails.password', [
                'user' => $user,
                'password' => $password,
            ], function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject('Your Account Password');
            });
        } catch (\Exception $e) {
            Log::error('Failed to send password email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e; // Re-throw to see the error
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
        
        // Check if WinSMS API is configured
        $apiKey = config('winsms.api_key', '');
        $useEmailFallback = empty($apiKey);
        
            if ($useEmailFallback) {
                // Fallback to email-to-SMS: send email to phone@winsms.net
                $smsEmail = $user->phone . '@winsms.net';
                
                try {
                    Mail::mailer('smtp')->send('emails.password', [
                        'user' => $user,
                        'password' => $password,
                    ], function ($message) use ($user, $smsEmail) {
                        $message->to($smsEmail, $user->name)
                            ->subject('Your Account Password');
                    });
                    Log::info('Password sent via email-to-SMS (winsms.net)', [
                        'user_id' => $user->id,
                        'phone' => $user->phone,
                        'email' => $smsEmail,
                    ]);
            } catch (\Exception $e) {
                Log::error('Failed to send password via email-to-SMS', [
                    'user_id' => $user->id,
                    'phone' => $user->phone,
                    'email' => $smsEmail,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        } else {
            // Use WinSMS API directly
            $this->winSMSService->sendSMS($user->phone, $message);
        }
    }

    /**
     * Generate and send password reset code
     *
     * @param User $user
     * @return string The reset code
     */
    /**
     * Generate and send password reset code
     *
     * @param User $user
     * @param string|null $method 'email', 'sms', or null (auto-detect based on identifier type)
     * @return string The reset code
     */
    public function sendPasswordReset(User $user, ?string $method = null): string
    {
        // Generate 6-digit code
        $code = str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store reset code in database (will be handled by PasswordResetCode model)
        // This method just generates and sends the code
        
        $message = "Your password reset code is: {$code}. This code expires in 15 minutes.";
        
        // Determine sending method
        // If method is 'phone' or 'sms', send via SMS
        // If method is 'email', send via email
        // If method is null, prefer email if available, otherwise SMS
        
        $useSMS = false;
        
        if ($method === 'phone' || $method === 'sms') {
            $useSMS = true;
        } elseif ($method === 'email') {
            $useSMS = false;
        } elseif ($method === null) {
            // Auto-detect: prefer email if available, otherwise SMS
            $useSMS = !$user->email && $user->phone;
        }
        
        if ($useSMS) {
            // Send via SMS using WinSMS API or email-to-SMS fallback
            if (!$user->phone) {
                Log::error('Cannot send SMS: user has no phone number', ['user_id' => $user->id]);
                throw new \Exception('User does not have a phone number for SMS reset');
            }
            
            // Check if WinSMS API is configured
            $apiKey = config('winsms.api_key', '');
            $useEmailFallback = empty($apiKey);
            
            if ($useEmailFallback) {
                // Fallback to email-to-SMS: send email to phone@winsms.net
                $smsEmail = $user->phone . '@winsms.net';
                
                try {
                    Mail::mailer('smtp')->send('emails.password-reset', [
                        'user' => $user,
                        'code' => $code,
                    ], function ($message) use ($user, $smsEmail) {
                        $message->to($smsEmail, $user->name)
                            ->subject('Password Reset Code');
                    });
                    Log::info('Password reset code sent via email-to-SMS (winsms.net)', [
                        'user_id' => $user->id,
                        'phone' => $user->phone,
                        'email' => $smsEmail,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send password reset via email-to-SMS', [
                        'user_id' => $user->id,
                        'phone' => $user->phone,
                        'email' => $smsEmail,
                        'error' => $e->getMessage(),
                    ]);
                    throw $e;
                }
            } else {
                // Use WinSMS API directly
                try {
                    $sent = $this->winSMSService->sendSMS($user->phone, $message);
                    if (!$sent) {
                        Log::error('Failed to send password reset SMS', [
                            'user_id' => $user->id,
                            'phone' => $user->phone,
                        ]);
                        throw new \Exception('Failed to send password reset code via SMS. Please check WinSMS configuration.');
                    }
                    Log::info('Password reset code sent via SMS', [
                        'user_id' => $user->id,
                        'phone' => $user->phone,
                    ]);
                } catch (\Exception $e) {
                    Log::error('WinSMS error during password reset', [
                        'user_id' => $user->id,
                        'phone' => $user->phone,
                        'error' => $e->getMessage(),
                    ]);
                    throw $e;
                }
            }
        } else {
            // Send via Email
            if (!$user->email) {
                Log::error('Cannot send email: user has no email address', ['user_id' => $user->id]);
                throw new \Exception('User does not have an email address for email reset');
            }
            
            try {
                // Force SMTP mailer to ensure emails are actually sent
                Mail::mailer('smtp')->send('emails.password-reset', [
                    'user' => $user,
                    'code' => $code,
                ], function ($message) use ($user) {
                    $message->to($user->email, $user->name)
                        ->subject('Password Reset Code');
                });
                Log::info('Password reset code sent via email', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send password reset email', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e; // Re-throw to see the error
            }
        }
        
        return $code;
    }
}
