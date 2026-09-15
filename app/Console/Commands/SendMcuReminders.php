<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Contractor;
use App\Models\McuNotificationLog;
use Carbon\Carbon;
use App\Jobs\SendMcuWhatsAppJob;
use App\Jobs\SendMcuExpiredWhatsAppJob;
use App\Jobs\SendMcuContractorEmailJob;
use App\Jobs\SendMcuExpiredContractorEmailJob;
use App\Models\McuRecord;

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
            } elseif ($diffDays < 0) {
                // EXPIRED
                // Pastikan belum dire-schedule (status != Rescheduled atau Pending sesudah expiry date)
                $hasRescheduled = $user->mcuRecords()
                                       ->whereIn('status', ['Rescheduled', 'Pending'])
                                       ->whereDate('created_at', '>=', $nextMcu)
                                       ->exists();
                
                if (!$hasRescheduled) {
                    $stage = 'MCU_EXPIRED';
                }
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

            if ($stage === 'MCU_EXPIRED') {
                // Auto create rescheduled record
                McuRecord::create([
                    'employee_id' => $user->id,
                    'mcu_year' => $today->year,
                    'mcu_date' => $today->toDateString(),
                    'status' => 'Rescheduled',
                ]);
                
                SendMcuExpiredWhatsAppJob::dispatch($user, $nextMcu->toDateString());
            } else {
                SendMcuWhatsAppJob::dispatch($user, $nextMcu->toDateString());
            }
            $this->info("Queued WA for User ID {$user->id} - Stage: {$stage}");
        }
    }

    private function processContractorGroup(Contractor $contractor, $stage, $users, Carbon $today)
    {
        $targetDate = null;
        if ($stage === 'MCU_H30') $targetDate = $today->copy()->addDays(30);
        elseif ($stage === 'MCU_H7') $targetDate = $today->copy()->addDays(7);
        elseif ($stage === 'MCU_H3') $targetDate = $today->copy()->addDays(3);
        elseif ($stage === 'MCU_EXPIRED') $targetDate = $today->copy(); // Gunakan hari ini untuk log rekap
        else $targetDate = $today->copy();

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

            if ($stage === 'MCU_EXPIRED') {
                $expiredEmployees = [];
                foreach ($users as $u) {
                    // Auto create rescheduled record
                    McuRecord::create([
                        'employee_id' => $u->id,
                        'mcu_year' => $today->year,
                        'mcu_date' => $today->toDateString(),
                        'status' => 'Rescheduled',
                    ]);

                    $expiredEmployees[] = [
                        'name' => $u->name,
                        'employee_id' => $u->employee_id,
                        'last_mcu_date' => $u->mcuRecords()->whereIn('status', ['Completed'])->latest('mcu_date')->value('mcu_date'),
                        'next_mcu_date' => $u->next_mcu_date
                    ];
                }
                SendMcuExpiredContractorEmailJob::dispatch($contractor->id, $targetDate->toDateString(), $expiredEmployees);
            } else {
                $userIds = collect($users)->pluck('id')->toArray();
                SendMcuContractorEmailJob::dispatch($log->id, $userIds);
            }
            $this->info("Queued Email for Contractor ID {$contractor->id} - Stage: {$stage}");
        }
    }
}
