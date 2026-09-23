<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Department;
use App\Models\Contractor;
use App\Models\McuRecord;
use App\Jobs\SendMcuDeptRecapEmailJob;
use App\Jobs\SendMcuContractorRecapEmailJob;
use Carbon\Carbon;
use App\Models\McuNotificationLog;

class SendMcuRecaps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mcu:send-recaps {--period= : The month to generate recap for (Y-m)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Monthly MCU Recaps to Departments and Contractors';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting MCU Recap Scheduler...');
        
        $period = $this->option('period') ? Carbon::createFromFormat('Y-m', $this->option('period'))->startOfMonth() : Carbon::now()->startOfMonth();
        $endOfMonth = $period->copy()->endOfMonth();
        $targetDate = $period->toDateString();

        $this->info("Generating recaps for period: " . $period->format('F Y'));

        // 1. Department Recaps (PT MSM & PT TTN)
        $departments = Department::whereNotNull('email')->get();
        foreach ($departments as $dept) {
            // Check if already sent
            $exists = McuNotificationLog::where('department_id', $dept->id)
                ->where('notification_stage', 'RECAP_DEPT')
                ->where('scheduled_date', $targetDate)
                ->where('status', 'Sent')
                ->exists();

            if ($exists) {
                continue;
            }

            // Find records for this department in the period
            $records = McuRecord::whereHas('employee', function($q) use ($dept) {
                $q->whereIn('company_name', ['PT MSM', 'PT TTN'])
                  ->whereHas('departments', function($q2) use ($dept) {
                      $q2->where('departments.id', $dept->id);
                  });
            })->whereBetween('mcu_date', [$period->toDateString(), $endOfMonth->toDateString()])->get();

            if ($records->isNotEmpty()) {
                $exportData = [];
                foreach ($records as $rec) {
                    $exportData[] = [
                        $rec->employee->employee_id ?? $rec->employee->id,
                        $rec->employee->name,
                        $dept->department_name,
                        $rec->mcu_date,
                        $rec->attendance_status,
                        $rec->process_status
                    ];
                }

                // Log and Queue
                McuNotificationLog::create([
                    'department_id' => $dept->id,
                    'notification_stage' => 'RECAP_DEPT',
                    'channel' => 'email',
                    'scheduled_date' => $targetDate,
                    'status' => 'Queued',
                ]);

                SendMcuDeptRecapEmailJob::dispatch($dept, $exportData, $targetDate);
                $this->info("Queued Dept Recap for: " . $dept->department_name);
            }
        }

        // 2. Contractor Recaps
        $contractors = Contractor::whereNotNull('email')->get();
        foreach ($contractors as $contractor) {
            // Check if already sent
            $exists = McuNotificationLog::where('contractor_id', $contractor->id)
                ->where('notification_stage', 'RECAP_CONTRACTOR')
                ->where('scheduled_date', $targetDate)
                ->where('status', 'Sent')
                ->exists();

            if ($exists) {
                continue;
            }

            // Find records for this contractor in the period
            $records = McuRecord::whereHas('employee', function($q) use ($contractor) {
                $q->whereHas('contractors', function($q2) use ($contractor) {
                    $q2->where('contractors.id', $contractor->id);
                });
            })->whereBetween('mcu_date', [$period->toDateString(), $endOfMonth->toDateString()])->get();

            if ($records->isNotEmpty()) {
                $exportData = [];
                foreach ($records as $rec) {
                    $exportData[] = [
                        $rec->employee->employee_id ?? $rec->employee->id,
                        $rec->employee->name,
                        $contractor->contractor_name,
                        $rec->mcu_date,
                        $rec->attendance_status,
                        $rec->process_status
                    ];
                }

                // Log and Queue
                McuNotificationLog::create([
                    'contractor_id' => $contractor->id,
                    'notification_stage' => 'RECAP_CONTRACTOR',
                    'channel' => 'email',
                    'scheduled_date' => $targetDate,
                    'status' => 'Queued',
                ]);

                SendMcuContractorRecapEmailJob::dispatch($contractor, $exportData, $targetDate);
                $this->info("Queued Contractor Recap for: " . $contractor->contractor_name);
            }
        }

        $this->info('MCU Recap Scheduler finished.');
    }
}
