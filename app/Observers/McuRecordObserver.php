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
        if ($mcuRecord->isDirty('mcu_date') || $mcuRecord->isDirty('status')) {
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

        // Get the latest valid MCU date
        // Valid means it has a date and status is not Pending, Rescheduled, or Cancelled.
        $latestRecord = McuRecord::where('employee_id', $employeeId)
            ->whereNotNull('mcu_date')
            ->whereNotIn('status', ['Cancelled', 'Pending', 'Rescheduled'])
            ->orderBy('mcu_date', 'desc')
            ->first();

        if ($latestRecord) {
            $nextDate = Carbon::parse($latestRecord->mcu_date)->addYear();
            
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
