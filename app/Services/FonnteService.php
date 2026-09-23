<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected $token;
    protected $url;

    public function __construct()
    {
        $this->token = config('services.fonnte.token', env('FONNTE_TOKEN'));
        $this->url = 'https://api.fonnte.com/send';
    }

    /**
     * Send a WhatsApp message via Fonnte API.
     *
     * @param string $target
     * @param string $message
     * @return array
     */
    public function sendMessage(string $target, string $message): array
    {
        $formattedTarget = $this->formatPhoneNumber($target);

        if (!$this->token) {
            Log::error('Fonnte API Token is missing.');
            return ['status' => false, 'message' => 'API Token missing'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->post($this->url, [
                'target' => $formattedTarget,
                'message' => $message,
            ]);

            $result = $response->json();

            if (!$response->successful() || !($result['status'] ?? false)) {
                Log::error('Fonnte API Error: ' . json_encode($result));
            }

            return $result ?? ['status' => false, 'message' => 'Unknown error'];
        } catch (\Exception $e) {
            Log::error('Fonnte Exception: ' . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Format phone number to Indonesian format starting with 62.
     *
     * @param string $number
     * @return string
     */
    public function formatPhoneNumber(string $number): string
    {
        // Remove non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $number);

        // Replace leading 0 with 62
        if (substr($number, 0, 1) === '0') {
            $number = '62' . substr($number, 1);
        }

        return $number;
    }
}
