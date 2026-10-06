<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\McuNotificationLog;
use App\Mail\McuExpiredEmployeeMail;
use Carbon\Carbon;

class SendMcuExpiredEmailJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    public $user;
    public $targetDate;

    public function __construct(User $user, string $targetDate)
    {
        $this->user = $user;
        $this->targetDate = $targetDate;
    }

    public function handle(): void
    {
        if (empty($this->user->email)) {
            $this->logError('User missing email address');
            return;
        }

        $formattedDate = Carbon::parse($this->targetDate)->translatedFormat('d F Y');

        try {
            Mail::to($this->user->email)->send(new McuExpiredEmployeeMail($this->user->name, $formattedDate));
            $this->updateLogStatus('sent');
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
        }
    }

    private function updateLogStatus(string $status, ?string $errorMessage = null)
    {
        McuNotificationLog::where('user_id', $this->user->id)
            ->where('notification_stage', 'MCU_EXPIRED_EMAIL')
            ->where('scheduled_date', $this->targetDate)
            ->update([
                'status' => $status,
                'sent_at' => $status === 'sent' ? now() : null,
                'error_message' => $errorMessage
            ]);
    }

    private function logError(string $message)
    {
        Log::error("SendMcuExpiredEmailJob Error [User: {$this->user->id}]: {$message}");
        $this->updateLogStatus('failed', $message);
    }
}
