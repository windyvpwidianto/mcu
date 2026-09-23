<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\McuRecord;
use App\Models\McuNotificationLog;
use Carbon\Carbon;
use App\Jobs\SendMcuNoShowRescheduleWhatsAppJob;

class ProcessMcuNoShow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mcu:process-no-show';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process no-show for MCU schedules that passed the end of the day without results.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting MCU No-Show Processor...');
        
        $today = Carbon::today('Asia/Jakarta');
        
        // Cari record yang mcu_date-nya hari ini, masih 'scheduled', dan tidak memiliki result
        $records = McuRecord::where('mcu_date', $today->toDateString())
            ->where('process_status', 'scheduled')
            ->doesntHave('result')
            ->get();

        $this->info("Found {$records->count()} records to process.");

        foreach ($records as $record) {
            // Mark as no_show and absent
            $record->update([
                'attendance_status' => 'absent',
                'process_status' => 'no_show',
                'notification_status' => 'notified' // avoid old notification triggers
            ]);

            // Create new record for H+1
            $newDate = $today->copy()->addDay();
            
            $newRecord = McuRecord::create([
                'employee_id' => $record->employee_id,
                'mcu_schedule_id' => $record->mcu_schedule_id, // keep link to schedule definition if needed
                'mcu_year' => $newDate->year,
                'mcu_date' => $newDate->toDateString(),
                'attendance_status' => 'scheduled',
                'process_status' => 'scheduled',
                'notification_status' => 'pending',
                'reschedule_count' => ($record->reschedule_count ?? 0) + 1,
            ]);

            // Link old record to new record
            $record->update(['rescheduled_to_id' => $newRecord->id]);

            // Update user next_mcu_date if it was today
            $user = $record->employee;
            if ($user && $user->next_mcu_date == $today->toDateString()) {
                $user->update(['next_mcu_date' => $newDate->toDateString()]);
            }

            // Create log to prevent duplicate sending in the future
            McuNotificationLog::create([
                'user_id' => $user->id,
                'notification_stage' => 'MCU_NOSHOW_RESCHEDULE',
                'channel' => 'whatsapp',
                'scheduled_date' => $newDate->toDateString(),
                'status' => 'Queued',
            ]);

            // Queue notification
            SendMcuNoShowRescheduleWhatsAppJob::dispatch($newRecord, $today->toDateString());
            
            $this->info("Processed No-Show for User ID: {$record->employee_id}");
        }

        $this->info('MCU No-Show Processor finished.');
    }
}
