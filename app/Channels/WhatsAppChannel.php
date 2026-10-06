<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    /**
     * Kirim notifikasi.
     *
     * @param  object  $notifiable (Model User/Entitas yang menerima)
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send(object $notifiable, Notification $notification): void
    {
        // Pastikan class notifikasi memiliki method 'toWhatsApp'
        if (! method_exists($notification, 'toWhatsApp')) {
            return;
        }

        // Ambil data array (phone dan message) dari method toWhatsApp()
        $messageData = $notification->toWhatsApp($notifiable);

        $phone = $messageData['phone'] ?? null;
        $message = $messageData['message'] ?? null;

        // Ubah baris pengecekan kosong ini:
        if (empty($phone) || empty($message)) {
            $notifiableId = $notifiable->id ?? 'Manual/Anonymous'; // Tambahkan fallback
            Log::warning("Gagal mengirim WA: Nomor telepon tidak ditemukan untuk User ID {$notifiableId}");
            return;
        }

        try {
            $url = config('services.whatsapp.url', 'https://api.fonnte.com/send');
            $host = parse_url($url, PHP_URL_HOST);
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

            // Contoh implementasi HTTP Request ke API WhatsApp (Misal: Fonnte)
            $response = Http::withoutVerifying()
                ->withOptions($options)
                ->withHeaders([
                    'Authorization' => config('services.whatsapp.token'), // Ambil token dari config
                ])->post($url, [
                    'target' => $phone,
                    'message' => $message,
                    'delay' => '2', // Mencegah pemblokiran anti-spam jika mengirim ke nomor yang sama berkali-kali dalam 1 detik
                ]);

            // Jika API merespon error, catat di log laravel (storage/logs/laravel.log)
            if ($response->failed()) {
                Log::error('WhatsApp API Failed: ' . $response->body());
            }
        } catch (\Exception $e) {
            // Tangkap error jika server API mati / timeout
            Log::error('WhatsApp Request Exception: ' . $e->getMessage());
        }
    }
}
