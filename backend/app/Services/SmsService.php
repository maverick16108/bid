<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $login;
    protected $password;
    protected $baseUrl = 'https://smsc.ru/sys/send.php';

    public function __construct()
    {
        $this->login = config('services.smsc.login');
        $this->password = config('services.smsc.password');
    }

    /**
     * Send SMS to a phone number.
     *
     * @param string $phone
     * @param string $message
     * @return bool
     */
    public function send(string $phone, string $message): bool
    {
        if (app()->environment('local', 'testing') && !config('services.smsc.enabled', false)) {
            Log::info("SMS Mock: To {$phone}: {$message}");
            return true;
        }

        try {
            $response = Http::get($this->baseUrl, [
                'login' => $this->login,
                'psw' => $this->password,
                'phones' => $phone,
                'mes' => $message,
                'fmt' => 3 // JSON format
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['error'])) {
                    Log::error("SMSC Error: " . json_encode($data));
                    return false;
                }
                return true;
            }

            Log::error("SMSC HTTP Error: " . $response->status());
            return false;
        } catch (\Exception $e) {
            Log::error("SMS Service Exception: " . $e->getMessage());
            return false;
        }
    }
}
