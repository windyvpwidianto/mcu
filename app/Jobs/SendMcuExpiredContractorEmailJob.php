<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Contractor;
use App\Models\User;
use App\Models\McuNotificationLog;
use App\Mail\McuExpiredContractorMail;

class SendMcuExpiredContractorEmailJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    public $contractorId;
    public $targetDate;
    public $expiredEmployees; // Array of employee details

    public function __construct(int $contractorId, string $targetDate, array $expiredEmployees)
    {
        $this->contractorId = $contractorId;
        $this->targetDate = $targetDate;
        $this->expiredEmployees = $expiredEmployees;
    }

    public function handle(): void
    {
        $contractor = Contractor::find($this->contractorId);
        if (!$contractor) return;

        // Admin Kontraktor adalah User yang berelasi dengan Contractor ini 
        // dan memiliki Role "Admin Contractor" atau "Admin"
        $adminContractors = User::whereHas('contractors', function ($query) {
            $query->where('contractors.id', $this->contractorId);
        })->whereHas('roles', function ($query) {
            $query->whereIn('name', ['Admin Contractor', 'Admin']);
        })->whereNotNull('email')->get();

        if ($adminContractors->isEmpty()) {
            $this->logError('No valid Admin Contractor email found.');
            return;
        }

        try {
            Mail::to($adminContractors)->send(new McuExpiredContractorMail($contractor->contractor_name, $this->expiredEmployees));
            $this->updateLogStatus('sent');
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
        }
    }

    private function updateLogStatus(string $status, ?string $errorMessage = null)
    {
        McuNotificationLog::where([
            'contractor_id' => $this->contractorId,
            'notification_stage' => 'MCU_EXPIRED_CONTRACTOR',
            'scheduled_date' => $this->targetDate,
        ])->update([
            'status' => $status,
            'sent_at' => $status === 'sent' ? now() : null,
            'error_message' => $errorMessage
        ]);
    }

    private function logError(string $message)
    {
        Log::error("SendMcuExpiredContractorEmailJob Error [Contractor: {$this->contractorId}]: {$message}");
        $this->updateLogStatus('failed', $message);
    }
}
