<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\McuNotificationLog;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\McuContractorSummaryMail;

class SendMcuContractorEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $logId;
    protected $userIds;

    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct($logId, $userIds)
    {
        $this->logId = $logId;
        $this->userIds = $userIds;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $notifLog = McuNotificationLog::with('contractor')->find($this->logId);

        if (!$notifLog || !$notifLog->contractor) return;
        if ($notifLog->status === 'Sent') return;

        $notifLog->increment('attempts');
        $contractor = $notifLog->contractor;

        // In Tosar, contractor might not have a direct 'email' column, 
        // Admin Contractor usually are Users linked to Contractor, or company email.
        // If they have users with 'contractor_id' and role Admin:
        $admins = $contractor->users()->whereHas('roles', function($q) {
            $q->whereIn('name', ['Admin Contractor', 'Admin']);
        })->get();

        if ($admins->isEmpty()) {
            $this->failLog($notifLog, 'No Admin Contractor found for this contractor.');
            return;
        }

        $usersToNotify = User::whereIn('id', $this->userIds)->get();

        try {
            foreach ($admins as $admin) {
                if ($admin->email) {
                    Mail::to($admin->email)->send(new McuContractorSummaryMail($contractor, $usersToNotify, $notifLog->notification_stage));
                }
            }

            $notifLog->update([
                'status' => 'Sent',
                'sent_at' => now(),
                'error_message' => null,
            ]);

        } catch (\Exception $e) {
            $this->failLog($notifLog, $e->getMessage());
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
