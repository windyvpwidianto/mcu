<?php

namespace App\Observers;

use App\Models\McuRecord;
use App\Models\User;
use Carbon\Carbon;

class McuRecordObserver
{
    /**
     * Handle the McuRecord "created" event.
     */
    public function created(McuRecord $mcuRecord): void
    {
        $this->updateNextMcuDate($mcuRecord->employee_id);
    }

    /**
     * Handle the McuRecord "updated" event.
     */
    public function updated(McuRecord $mcuRecord): void
    {
        // Only update if mcu_date or status changed
        if ($mcuRecord->isDirty('mcu_date') || $mcuRecord->isDirty('process_status') || $mcuRecord->isDirty('attendance_status')) {
            $this->updateNextMcuDate($mcuRecord->employee_id);
        }
    }

    /**
     * Handle the McuRecord "deleted" event.
     */
    public function deleted(McuRecord $mcuRecord): void
    {
        $this->updateNextMcuDate($mcuRecord->employee_id);
    }

    /**
     * Handle the McuRecord "restored" event.
     */
    public function restored(McuRecord $mcuRecord): void
    {
        $this->updateNextMcuDate($mcuRecord->employee_id);
    }

    /**
     * Handle the McuRecord "force deleted" event.
     */
    public function forceDeleted(McuRecord $mcuRecord): void
    {
        $this->updateNextMcuDate($mcuRecord->employee_id);
    }

    /**
     * Calculate and update the next_mcu_date for the user
     */
    protected function updateNextMcuDate($employeeId)
    {
        if (!$employeeId) return;

        $user = User::find($employeeId);
        if (!$user) return;

        // Check for upcoming schedules first
        $upcomingRecord = McuRecord::where('employee_id', $employeeId)
            ->whereNotNull('mcu_date')
            ->where('process_status', 'scheduled')
            ->orderBy('mcu_date', 'asc')
            ->first();

        $nextDate = null;

        if ($upcomingRecord) {
            $nextDate = Carbon::parse($upcomingRecord->mcu_date);
        } else {
            // Get the latest completed MCU
            $latestCompleted = McuRecord::where('employee_id', $employeeId)
                ->whereNotNull('mcu_date')
                ->where('process_status', 'completed')
                ->where('attendance_status', 'present')
                ->orderBy('mcu_date', 'desc')
                ->first();

            if ($latestCompleted) {
                $nextDate = Carbon::parse($latestCompleted->mcu_date)->addYear();
            }
        }

        if ($nextDate) {
            // Only save if it's different to prevent recursive loops or unnecessary writes
            if (!$user->next_mcu_date || !Carbon::parse($user->next_mcu_date)->isSameDay($nextDate)) {
                $user->next_mcu_date = $nextDate->toDateString();
                $user->saveQuietly(); // Use saveQuietly if User observer triggers other things
            }
        } else {
            // If no records, set to null
            if ($user->next_mcu_date !== null) {
                $user->next_mcu_date = null;
                $user->saveQuietly();
            }
        }
    }
}
