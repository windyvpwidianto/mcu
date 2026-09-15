<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\McuRecord;
use Carbon\Carbon;

class SyncNextMcuDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mcu:sync-next-date';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync historical next_mcu_date for all existing users based on their latest MCU record';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting MCU Date synchronization...');

        $users = User::whereHas('mcuRecords')->get();
        $count = 0;
        
        foreach ($users as $user) {
            $latestRecord = McuRecord::where('employee_id', $user->id)
                ->whereNotNull('mcu_date')
                ->where('status', '!=', 'Cancelled')
                ->orderBy('mcu_date', 'desc')
                ->first();

            if ($latestRecord) {
                $nextDate = Carbon::parse($latestRecord->mcu_date)->addYear()->toDateString();
                
                if ($user->next_mcu_date !== $nextDate) {
                    $user->next_mcu_date = $nextDate;
                    $user->saveQuietly();
                    $count++;
                }
            }
        }

        $this->info("Successfully synchronized next_mcu_date for {$count} users.");
    }
}
