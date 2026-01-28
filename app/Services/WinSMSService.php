<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WinSMSService
{
    protected string $apiKey;
    protected ?string $username;
    protected ?string $senderId;

    public function __construct()
    {
        $this->apiKey = config('winsms.api_key', '');
        $this->username = config('winsms.username');
        $this->senderId = config('winsms.sender_id');
    }

    /**
     * Format phone number for WinSMS
     * Ensures phone number is in proper format (e.g., 27812345678)
     *
     * @param string $phone
     * @return string
     */
    public function formatPhone(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 0, replace with 27 (South Africa country code)
        if (strlen($phone) == 10 && substr($phone, 0, 1) === '0') {
            $phone = '27' . substr($phone, 1);
        }

        // If doesn't start with country code, add 27
        if (strlen($phone) == 9) {
            $phone = '27' . $phone;
        }

        return $phone;
    }

    /**
     * Send SMS via WinSMS API
     *
     * @param string $phone
     * @param string $message
     * @return bool
     */
    public function sendSMS(string $phone, string $message): bool
    {
        if (empty($this->apiKey)) {
            Log::warning('WinSMS API key not configured');
            return false;
        }

        $formattedPhone = $this->formatPhone($phone);

        try {
            $url = 'https://www.winsms.co.za/api/batchmessage.asp';
            
            $params = [
                'ApiKey' => $this->apiKey,
                'Numbers' => $formattedPhone,
                'Message' => $message,
            ];

            if ($this->username) {
                $params['Username'] = $this->username;
            }

            if ($this->senderId) {
                $params['SenderId'] = $this->senderId;
            }

            $response = Http::asForm()->post($url, $params);

            if ($response->successful()) {
                $body = $response->body();
                
                // WinSMS returns success indicators in the response body
                // Check for success patterns (adjust based on actual API response format)
                if (strpos($body, 'OK') !== false || strpos($body, 'SUCCESS') !== false) {
                    Log::info('WinSMS sent successfully', ['phone' => $formattedPhone]);
                    return true;
                }
            }

            Log::error('WinSMS API error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'phone' => $formattedPhone,
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('WinSMS exception: ' . $e->getMessage(), [
                'phone' => $formattedPhone,
                'exception' => $e,
            ]);
            return false;
        }
    }
}
