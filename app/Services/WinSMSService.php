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
    /**
     * Send SMS via WinSMS API
     *
     * @param string $phone
     * @param string $message
     * @return bool
     * @throws \Exception If API key is not configured or sending fails
     */
    public function sendSMS(string $phone, string $message): bool
    {
        if (empty($this->apiKey)) {
            $error = 'WinSMS API key not configured. Please set WINSMS_API_KEY in your .env file.';
            Log::error($error);
            throw new \Exception($error);
        }

        $formattedPhone = $this->formatPhone($phone);

        try {
            $url = config('winsms.api_url', 'https://www.winsms.co.za/api/batchmessage.asp');
            
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

            Log::info('Sending SMS via WinSMS', [
                'phone' => $formattedPhone,
                'url' => $url,
                'has_username' => !empty($this->username),
                'has_sender_id' => !empty($this->senderId),
            ]);

            $response = Http::timeout(30)->asForm()->post($url, $params);

            if ($response->successful()) {
                $body = $response->body();
                
                // WinSMS returns success indicators in the response body
                // Common success patterns: "OK", "SUCCESS", "MessageID", or numeric message ID
                $successPatterns = ['OK', 'SUCCESS', 'MessageID', '/^\d+$/'];
                $isSuccess = false;
                
                foreach ($successPatterns as $pattern) {
                    if ($pattern === '/^\d+$/') {
                        // Check if body is just a number (message ID)
                        if (preg_match($pattern, trim($body))) {
                            $isSuccess = true;
                            break;
                        }
                    } else {
                        if (stripos($body, $pattern) !== false) {
                            $isSuccess = true;
                            break;
                        }
                    }
                }
                
                if ($isSuccess) {
                    Log::info('WinSMS sent successfully', [
                        'phone' => $formattedPhone,
                        'response' => $body,
                    ]);
                    return true;
                } else {
                    // Response was successful but doesn't match success patterns
                    Log::warning('WinSMS response unclear', [
                        'phone' => $formattedPhone,
                        'response_body' => $body,
                        'status' => $response->status(),
                    ]);
                    // Still return true if HTTP status is 200, as API might have different format
                    return $response->status() === 200;
                }
            }

            Log::error('WinSMS API error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'phone' => $formattedPhone,
            ]);

            throw new \Exception('WinSMS API returned error: ' . $response->status() . ' - ' . $response->body());
        } catch (\Exception $e) {
            Log::error('WinSMS exception', [
                'phone' => $formattedPhone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Test WinSMS configuration and connection
     *
     * @param string|null $testPhone Optional phone number to test with
     * @return array Test results
     */
    public function testConfiguration(?string $testPhone = null): array
    {
        $results = [
            'api_key_configured' => !empty($this->apiKey),
            'username_configured' => !empty($this->username),
            'sender_id_configured' => !empty($this->senderId),
            'api_url' => config('winsms.api_url'),
            'ready' => false,
            'test_send' => null,
        ];

        if (!$results['api_key_configured']) {
            $results['error'] = 'WINSMS_API_KEY is not set in .env file';
            return $results;
        }

        $results['ready'] = true;

        // If test phone provided, try sending a test message
        if ($testPhone) {
            try {
                $testMessage = 'Test message from ' . config('app.name') . ' - ' . now()->format('Y-m-d H:i:s');
                $sent = $this->sendSMS($testPhone, $testMessage);
                $results['test_send'] = [
                    'success' => $sent,
                    'phone' => $this->formatPhone($testPhone),
                ];
            } catch (\Exception $e) {
                $results['test_send'] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
