<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\McuNotificationLog;
use Illuminate\Support\Facades\Log;
use App\Services\FonnteService;
use Carbon\Carbon;

class SendMcuExpiredWhatsAppJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    public $user;
    public $targetDate;

    public function __construct(User $user, string $targetDate)
    {
        $this->user = $user;
        $this->targetDate = $targetDate;
    }

    public function handle(FonnteService $fonnteService): void
    {
        $phone = $this->user->phone_number ?? $this->user->whatsapp_number;
        if (!$phone) {
            $this->logError('User missing WhatsApp number');
            return;
        }

        $formattedDate = Carbon::parse($this->targetDate)->translatedFormat('d F Y');

        $message = "Yth. Bapak/Ibu {$this->user->name},\n\n";
        $message .= "Kami informasikan bahwa masa berlaku Medical Check Up (MCU) Anda telah berakhir pada tanggal {$formattedDate}.\n\n";
        $message .= "Mohon untuk segera melakukan pendaftaran Medical Check Up (MCU) terbaru dengan menghubungi OHS Department.\n\n";
        $message .= "Terima kasih atas perhatian dan kerja samanya dalam menjaga kesehatan dan keselamatan kerja.\n\n";
        $message .= "OHS Department";

        try {
            $response = $fonnteService->sendMessage($phone, $message);
            
            if (isset($response['status']) && $response['status']) {
                $this->updateLogStatus('sent');
            } else {
                $this->logError('Fonnte API returned false status');
            }
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
        }
    }

    private function updateLogStatus(string $status, ?string $errorMessage = null)
    {
        McuNotificationLog::where('user_id', $this->user->id)
            ->whereIn('notification_stage', ['MCU_EXPIRED', 'MCU_EXPIRED_EMPLOYEE'])
            ->where('scheduled_date', $this->targetDate)
            ->update([
                'status' => $status,
                'sent_at' => $status === 'sent' ? now() : null,
                'error_message' => $errorMessage
            ]);
    }

    private function logError(string $message)
    {
        Log::error("SendMcuExpiredWhatsAppJob Error [User: {$this->user->id}]: {$message}");
        $this->updateLogStatus('failed', $message);
    }
}
