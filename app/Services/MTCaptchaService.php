<?php

namespace App\Services;

use App\Settings\AccountSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MTCaptchaService
{
    protected AccountSettings $accountSettings;

    public function __construct(AccountSettings $accountSettings)
    {
        $this->accountSettings = $accountSettings;
    }

    /**
     * Validate MTCaptcha token
     *
     * @param string|null $token
     * @return bool
     */
    public function validateToken(?string $token): bool
    {
        // Skip validation if MTCaptcha is disabled
        if (!$this->accountSettings->mtcaptcha_enabled) {
            return true;
        }

        // Skip validation on localhost
        if (app()->environment('local') || request()->getHost() === 'localhost' || request()->getHost() === '127.0.0.1') {
            return true;
        }

        // If no token provided, validation fails
        if (empty($token)) {
            return false;
        }

        try {
            $response = Http::get('https://service.mtcaptcha.com/mtcv1/api/checktoken', [
                'privatekey' => $this->accountSettings->mtcaptcha_private_key,
                'token' => $token,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Check if validation was successful
                if (isset($data['success']) && $data['success'] === true) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('MTCaptcha validation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if MTCaptcha should be shown
     *
     * @return bool
     */
    public function shouldShow(): bool
    {
        // Show if MTCaptcha is enabled (including on localhost for testing)
        return $this->accountSettings->mtcaptcha_enabled;
    }

    /**
     * Get MTCaptcha site key
     *
     * @return string
     */
    public function getSiteKey(): string
    {
        return $this->accountSettings->mtcaptcha_site_key;
    }
}
