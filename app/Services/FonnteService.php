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
        $this->token = config('services.fonnte.token', env('FONNTE_TOKEN', config('services.whatsapp.token', env('WHATSAPP_API_TOKEN'))));
        $this->url = config('services.fonnte.url', env('FONNTE_URL', config('services.whatsapp.url', 'https://api.fonnte.com/send')));
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
            $host = parse_url($this->url, PHP_URL_HOST);
            $ip = gethostbyname($host);
            if ($ip === $host && $host === 'api.fonnte.com') {
                $ip = '103.52.212.50';
            }

            $options = [];
            if ($ip && $ip !== $host) {
                $options['curl'] = [
                    CURLOPT_RESOLVE => [
                        "{$host}:443:{$ip}",
                        "{$host}:80:{$ip}",
                    ]
                ];
            }

            $response = Http::withoutVerifying()
                ->withOptions($options)
                ->withHeaders([
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
