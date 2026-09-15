<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Contractor;
use App\Models\McuNotificationLog;
use Carbon\Carbon;
use App\Jobs\SendMcuWhatsAppJob;
use App\Jobs\SendMcuContractorEmailJob;

class SendMcuReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mcu:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send MCU Reminders for upcoming schedules (H-30, H-7, H-3, Overdue)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting MCU Reminder Scheduler...');
        $today = Carbon::today();

        // Ambil users yang memiliki next_mcu_date
        $users = User::whereNotNull('next_mcu_date')->with('contractors', 'departments')->get();

        $contractorData = [];

        foreach ($users as $user) {
            $nextMcu = Carbon::parse($user->next_mcu_date);
            $diffDays = $today->diffInDays($nextMcu, false); // false agar bisa negatif (past)
            
            $stage = null;
            if ($diffDays == 30) {
                $stage = 'MCU_H30';
            } elseif ($diffDays == 7) {
                $stage = 'MCU_H7';
            } elseif ($diffDays == 3) {
                $stage = 'MCU_H3';
            } elseif ($diffDays < 0 && $diffDays > -30) {
                // Biar tidak di-spam, kita peringatkan saat overdue pertama kali (misal H+1 atau sesuai logic)
                // Kita sederhanakan logic Overdue reminder (opsional, sesuaikan dengan request minimal: H-30, H-7, H-3)
                // User requirement: H-30, H-7, H-3
                // Kita lewati overdue harian, hanya tracking status
            }

            if (!$stage) continue;

            $isContractor = $user->contractors->isNotEmpty();

            if (!$isContractor) {
                // INTERNAL EMPLOYEE - Send WhatsApp
                $this->processInternalEmployee($user, $stage, $nextMcu);
            } else {
                // CONTRACTOR - Grouping
                $contractor = $user->contractors->first();
                $contractorData[$contractor->id]['contractor'] = $contractor;
                if (!isset($contractorData[$contractor->id]['stages'][$stage])) {
                    $contractorData[$contractor->id]['stages'][$stage] = [];
                }
                $contractorData[$contractor->id]['stages'][$stage][] = $user;
            }
        }

        // Process Contractor Grouped Data
        foreach ($contractorData as $contractorId => $data) {
            $contractor = $data['contractor'];
            foreach ($data['stages'] as $stage => $users) {
                $this->processContractorGroup($contractor, $stage, $users, $today);
            }
        }

        $this->info('MCU Reminder Scheduler finished.');
    }

    private function processInternalEmployee(User $user, $stage, Carbon $nextMcu)
    {
        $exists = McuNotificationLog::where('user_id', $user->id)
            ->where('notification_stage', $stage)
            ->where('scheduled_date', $nextMcu->toDateString())
            ->exists();

        if (!$exists) {
            $log = McuNotificationLog::create([
                'user_id' => $user->id,
                'notification_stage' => $stage,
                'channel' => 'whatsapp',
                'scheduled_date' => $nextMcu->toDateString(),
                'status' => 'Queued',
            ]);

            SendMcuWhatsAppJob::dispatch($log->id);
            $this->info("Queued WA for User ID {$user->id} - Stage: {$stage}");
        }
    }

    private function processContractorGroup(Contractor $contractor, $stage, $users, Carbon $today)
    {
        // For contractor, the scheduled_date for the *batch* might be today or we can use today's date for grouping logic
        // Because a contractor batch could contain multiple next_mcu_date (if we group by stage). Wait, if they are H-30 today, they all have the same next_mcu_date.
        // Yes, if it's H-30 today, their next_mcu_date is today + 30 days. So it's safe to use that date.
        $targetDate = null;
        if ($stage === 'MCU_H30') $targetDate = $today->copy()->addDays(30);
        if ($stage === 'MCU_H7') $targetDate = $today->copy()->addDays(7);
        if ($stage === 'MCU_H3') $targetDate = $today->copy()->addDays(3);

        $exists = McuNotificationLog::where('contractor_id', $contractor->id)
            ->where('notification_stage', $stage)
            ->where('scheduled_date', $targetDate->toDateString())
            ->exists();

        if (!$exists) {
            $log = McuNotificationLog::create([
                'contractor_id' => $contractor->id,
                'notification_stage' => $stage,
                'channel' => 'email',
                'scheduled_date' => $targetDate->toDateString(),
                'status' => 'Queued',
            ]);

            // Pass the user IDs to the job so it knows who to include in the email
            $userIds = collect($users)->pluck('id')->toArray();
            SendMcuContractorEmailJob::dispatch($log->id, $userIds);
            $this->info("Queued Email for Contractor ID {$contractor->id} - Stage: {$stage}");
        }
    }
}
