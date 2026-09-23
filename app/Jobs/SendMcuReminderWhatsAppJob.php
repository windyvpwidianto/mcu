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

class SendMcuReminderWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $record;
    public $stage;
    public $scheduleDate;

    /**
     * Create a new job instance.
     */
    public function __construct(McuRecord $record, string $stage, string $scheduleDate)
    {
        $this->record = $record;
        $this->stage = $stage;
        $this->scheduleDate = $scheduleDate;
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
        $message .= "Ini adalah pengingat jadwal Medical Check Up (MCU) Anda yang akan datang pada:\n\n";
        $message .= "*Tanggal:* {$this->scheduleDate}\n";
        $message .= "*Status:* " . ($this->stage == 'MCU_H30' ? 'H-30' : ($this->stage == 'MCU_H7' ? 'H-7' : 'H-3')) . "\n\n";
        $message .= "Mohon persiapkan diri Anda dan hadir tepat waktu.\n\n";
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
            'notification_stage' => $this->stage,
            'scheduled_date' => $this->scheduleDate,
        ])->update([
            'status' => $status,
            'sent_at' => $status === 'Sent' ? now() : null,
            'error_message' => $errorMessage,
            'attempts' => \DB::raw('attempts + 1')
        ]);
    }

    private function logError(string $message)
    {
        Log::error("SendMcuReminderWhatsAppJob Error [Record: {$this->record->id}]: {$message}");
        $this->updateLogStatus('Failed', $message);
    }
}
