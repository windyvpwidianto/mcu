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
        if (!$this->user->whatsapp_number) {
            $this->logError('User missing WhatsApp number');
            return;
        }

        $formattedDate = Carbon::parse($this->targetDate)->translatedFormat('d F Y');

        $message = "Yth. *{$this->user->name}*,\n\n";
        $message .= "Kami menginformasikan bahwa masa berlaku MCU Anda telah berakhir pada:\n\n";
        $message .= "*{$formattedDate}*\n\n";
        $message .= "Saat ini MCU Anda berstatus *EXPIRED* dan memerlukan penjadwalan ulang.\n\n";
        $message .= "Informasi mengenai jadwal MCU berikutnya akan disampaikan lebih lanjut.\n\n";
        $message .= "Terima kasih.\n\n";
        $message .= "_Ini adalah pesan otomatis dari sistem TOSAR._";

        try {
            $response = $fonnteService->sendMessage($this->user->whatsapp_number, $message);
            
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
        McuNotificationLog::where([
            'user_id' => $this->user->id,
            'notification_stage' => 'MCU_EXPIRED_EMPLOYEE',
            'scheduled_date' => $this->targetDate,
        ])->update([
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
