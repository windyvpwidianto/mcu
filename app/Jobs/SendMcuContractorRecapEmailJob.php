<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Contractor;
use App\Models\McuNotificationLog;
use App\Mail\McuContractorRecapMail;
use Carbon\Carbon;

class SendMcuContractorRecapEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $contractor;
    public $records;
    public $targetDate;

    /**
     * Create a new job instance.
     */
    public function __construct(Contractor $contractor, array $records, string $targetDate)
    {
        $this->contractor = $contractor;
        $this->records = $records;
        $this->targetDate = $targetDate;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!$this->contractor->email) {
            $this->logError('Contractor email is missing');
            return;
        }

        $monthName = Carbon::parse($this->targetDate)->translatedFormat('F Y');

        try {
            Mail::to($this->contractor->email)->send(new McuContractorRecapMail($this->contractor, $this->records, $monthName));
            
            $this->updateLogStatus('Sent');
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
        }
    }

    private function updateLogStatus(string $status, ?string $errorMessage = null)
    {
        McuNotificationLog::where([
            'contractor_id' => $this->contractor->id,
            'notification_stage' => 'RECAP_CONTRACTOR',
            'scheduled_date' => $this->targetDate,
        ])->update([
            'status' => $status,
            'sent_at' => $status === 'Sent' ? now() : null,
            'error_message' => $errorMessage,
            'attempts' => \DB::raw('attempts + 1')
        ]);
    }

    private function logError(string $message)
    {
        Log::error("SendMcuContractorRecapEmailJob Error [Contractor: {$this->contractor->id}]: {$message}");
        $this->updateLogStatus('Failed', $message);
    }
}
