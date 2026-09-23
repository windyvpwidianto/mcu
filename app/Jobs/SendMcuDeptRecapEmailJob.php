<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Department;
use App\Models\McuNotificationLog;
use App\Mail\McuDepartmentRecapMail;
use Carbon\Carbon;

class SendMcuDeptRecapEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $department;
    public $records;
    public $targetDate;

    /**
     * Create a new job instance.
     */
    public function __construct(Department $department, array $records, string $targetDate)
    {
        $this->department = $department;
        $this->records = $records;
        $this->targetDate = $targetDate;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!$this->department->email) {
            $this->logError('Department email is missing');
            return;
        }

        $monthName = Carbon::parse($this->targetDate)->translatedFormat('F Y');

        try {
            Mail::to($this->department->email)->send(new McuDepartmentRecapMail($this->department, $this->records, $monthName));
            
            $this->updateLogStatus('Sent');
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
        }
    }

    private function updateLogStatus(string $status, ?string $errorMessage = null)
    {
        McuNotificationLog::where([
            'department_id' => $this->department->id,
            'notification_stage' => 'RECAP_DEPT',
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
        Log::error("SendMcuDeptRecapEmailJob Error [Dept: {$this->department->id}]: {$message}");
        $this->updateLogStatus('Failed', $message);
    }
}
