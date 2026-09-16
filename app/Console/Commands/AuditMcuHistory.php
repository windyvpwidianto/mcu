<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\McuRecord;
use Carbon\Carbon;

class AuditMcuHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mcu:audit-history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit MCU History 2023-2025, fix Last/Next MCU, and generate recursive reschedules';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai Audit Histori MCU...');
        
        $today = Carbon::today();
        $hardStopLimit = Carbon::parse('2026-09-16');
        $hardStopDate = Carbon::parse('2026-11-01');

        $users = User::all();
        $bar = $this->output->createProgressBar(count($users));

        foreach($users as $user) {
            // Cari MCU Aktual Terakhir (Last MCU)
            $latestCompleted = McuRecord::where('employee_id', $user->id)
                ->where('process_status', 'completed')
                ->where('attendance_status', 'present')
                ->orderBy('mcu_date', 'desc')
                ->first();

            if (!$latestCompleted) {
                $bar->advance();
                continue; // Jika belum pernah MCU, lewati atau tangani terpisah
            }

            // Hitung Next MCU Normal
            $baseNextDate = Carbon::parse($latestCompleted->mcu_date)->addYear();
            
            // Jika Next MCU Normal sudah lewat (Overdue/Expired)
            if ($baseNextDate->lt($today)) {
                $currentDate = $baseNextDate->copy();
                
                // Cari atau buat record Base (yang expired)
                $previousRecord = McuRecord::firstOrCreate([
                    'employee_id' => $user->id,
                    'mcu_date' => $currentDate->toDateString(),
                ], [
                    'mcu_year' => $currentDate->year,
                    'attendance_status' => 'absent',
                    'process_status' => 'rescheduled',
                    'notification_status' => 'notified',
                    'reschedule_count' => 0
                ]);
                
                // Pastikan statusnya benar (jika record sudah ada tapi belum expired)
                if ($previousRecord->process_status !== 'completed' && $previousRecord->attendance_status !== 'present') {
                    $previousRecord->update([
                        'attendance_status' => 'absent',
                        'process_status' => 'rescheduled',
                    ]);
                }

                $rescheduleCount = $previousRecord->reschedule_count;

                // Loop +3 bulan sampai melewati hari ini atau terkena hard stop
                while ($currentDate->lt($today)) {
                    $nextRescheduleDate = $currentDate->copy()->addMonths(3);
                    $rescheduleCount++;

                    // Hard Stop Rule
                    if ($nextRescheduleDate->gt($hardStopLimit)) {
                        $nextRescheduleDate = $hardStopDate->copy();
                    }

                    $isFuture = $nextRescheduleDate->gte($today);

                    // Cari child record
                    $childRecord = McuRecord::where('employee_id', $user->id)
                        ->where('mcu_date', $nextRescheduleDate->toDateString())
                        ->first();
                        
                    if (!$childRecord) {
                        $childRecord = McuRecord::create([
                            'employee_id' => $user->id,
                            'mcu_year' => $nextRescheduleDate->year,
                            'mcu_date' => $nextRescheduleDate->toDateString(),
                            'attendance_status' => $isFuture ? 'scheduled' : 'absent',
                            'process_status' => $isFuture ? 'scheduled' : 'rescheduled',
                            'notification_status' => $isFuture ? 'pending' : 'notified',
                            'reschedule_count' => $rescheduleCount
                        ]);
                    }

                    // Tautkan parent ke child (Anti Duplicate Chain)
                    if ($previousRecord->rescheduled_to_id !== $childRecord->id) {
                        $previousRecord->update(['rescheduled_to_id' => $childRecord->id]);
                    }

                    $currentDate = $nextRescheduleDate;
                    $previousRecord = $childRecord;

                    // Hentikan jika sudah mencapai hard stop date atau sudah di masa depan
                    if ($currentDate->eq($hardStopDate) || $currentDate->gte($today)) {
                        break;
                    }
                }

                // Final Next MCU date untuk User
                $user->next_mcu_date = $currentDate->toDateString();

            } else {
                // Next MCU di masa depan
                $user->next_mcu_date = $baseNextDate->toDateString();
            }

            $user->saveQuietly();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Audit Histori MCU Selesai!');
    }
}
