<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\McuNotificationLog;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class SendMcuWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $logId;

    public $tries = 3; // Max attempts

    /**
     * Create a new job instance.
     */
    public function __construct($logId)
    {
        $this->logId = $logId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $notifLog = McuNotificationLog::with('user')->find($this->logId);

        if (!$notifLog || !$notifLog->user) return;
        if ($notifLog->status === 'Sent') return; // Prevent duplicate send

        $notifLog->increment('attempts');
        $user = $notifLog->user;
        
        $phone = $user->whatsapp_number ?? $user->phone;
        
        if (!$phone) {
            $this->failLog($notifLog, 'No WhatsApp number found for user.');
            return;
        }

        $dateFormatted = Carbon::parse($notifLog->scheduled_date)->translatedFormat('d F Y');
        
        $days = 0;
        if ($notifLog->notification_stage == 'MCU_H30') $days = 30;
        if ($notifLog->notification_stage == 'MCU_H7') $days = 7;
        if ($notifLog->notification_stage == 'MCU_H3') $days = 3;

        $message = "*REMINDER MEDICAL CHECK-UP TAHUNAN*\n\n";
        $message .= "Halo Bapak/Ibu {$user->name},\n";
        $message .= "Ini adalah pengingat otomatis bahwa jadwal Medical Check-Up (MCU) tahunan Anda akan jatuh pada tanggal *{$dateFormatted}* ({$days} hari lagi).\n\n";
        $message .= "Mohon untuk mempersiapkan diri dan memastikan Anda dapat hadir pada jadwal yang ditentukan.\n\n";
        $message .= "Terima kasih atas perhatiannya.\n";
        $message .= "- *Klinik Perusahaan*";

        try {
            // Using existing WA logic in the app.
            // App\Channels\WhatsAppChannel uses specific endpoint. We can replicate it or use standard Http.
            // Assuming standard API based on previous knowledge:
            $apiUrl = env('WHATSAPP_API_URL', 'http://localhost:3000/send-message');
            
            $response = Http::post($apiUrl, [
                'number' => $phone,
                'message' => $message,
            ]);

            if ($response->successful()) {
                $notifLog->update([
                    'status' => 'Sent',
                    'sent_at' => now(),
                    'error_message' => null,
                ]);
            } else {
                throw new \Exception('WA API Error: ' . $response->body());
            }

        } catch (\Exception $e) {
            $this->failLog($notifLog, $e->getMessage());
            // Rethrow so it will be retried if attempts < max tries
            throw $e;
        }
    }

    private function failLog($notifLog, $error)
    {
        $notifLog->update([
            'status' => 'Failed',
            'error_message' => substr($error, 0, 500)
        ]);
    }
}
