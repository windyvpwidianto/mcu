<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\McuRecord;
use App\Models\McuNotificationLog;
use App\Services\FonnteService;
use Illuminate\Support\Facades\Log;

class SendMcuNoShowRescheduleWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $record;
    public $oldDate;

    /**
     * Create a new job instance.
     */
    public function __construct(McuRecord $record, string $oldDate)
    {
        $this->record = $record;
        $this->oldDate = $oldDate;
    }

    /**
     * Execute the job.
     */
    public function handle(FonnteService $fonnteService): void
    {
        $user = $this->record->employee;

        if (!$user || !$user->phone_number) {
            $this->logError('User or phone number is missing');
            return;
        }

        $message = "Yth. *{$user->name}*,\n\n";
        $message .= "Sistem mencatat bahwa Anda *Tidak Hadir (No-Show)* pada jadwal Medical Check Up (MCU) tanggal *{$this->oldDate}*.\n\n";
        $message .= "Oleh karena itu, jadwal Anda telah dialihkan (Reschedule) secara otomatis menjadi:\n\n";
        $message .= "*Tanggal Baru:* {$this->record->mcu_date}\n\n";
        $message .= "Mohon pastikan kehadiran Anda pada jadwal yang baru ini.\n\n";
        $message .= "Terima kasih.\n\n";
        $message .= "_Ini adalah pesan otomatis dari sistem TOSAR._";

        try {
            $response = $fonnteService->sendMessage($user->phone_number, $message);
            
            if (isset($response['status']) && $response['status']) {
                $this->updateLogStatus('Sent');
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
            'user_id' => $this->record->employee_id,
            'notification_stage' => 'MCU_NOSHOW_RESCHEDULE',
            'scheduled_date' => $this->record->mcu_date,
        ])->update([
            'status' => $status,
            'sent_at' => $status === 'Sent' ? now() : null,
            'error_message' => $errorMessage,
            'attempts' => \DB::raw('attempts + 1')
        ]);
    }

    private function logError(string $message)
    {
        Log::error("SendMcuNoShowRescheduleWhatsAppJob Error [Record: {$this->record->id}]: {$message}");
        $this->updateLogStatus('Failed', $message);
    }
}
