<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\McuMasterData;
use App\Notifications\McuReminderNotification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessMcuReminders extends Command
{
    protected $signature = 'mcu:process-reminders';
    protected $description = 'Proses notifikasi H-2 bulan, H-1 bulan, H-1 minggu untuk MCU tahunan';

    public function handle()
    {
        $today = Carbon::today();

        // 1. Cek jika mcu_date sudah lewat, kita bisa set status menjadi 'overdue' jika diperlukan
        // Namun sesuai rencana, tidak ada lagi auto-rollover otomatis.
        $pastMcus = McuMasterData::whereNotNull('mcu_date')->whereDate('mcu_date', '<', $today)
            ->where('notification_status', '!=', 'overdue')->get();
        foreach ($pastMcus as $mcu) {
            $mcu->update([
                'notification_status' => 'overdue'
            ]);
        }

        // 2. Peta eskalasi aturan reminder
        $reminders = [
            'h-2_bulan' => [
                'type' => 'h-2_bulan', 
                'statuses' => ['pending'], 
                'max_days' => 60, 
                'min_days' => 31,
                'next' => 'h-2_bulan'
            ],
            'h-1_bulan' => [
                'type' => 'h-1_bulan', 
                'statuses' => ['pending', 'h-2_bulan'], 
                'max_days' => 30, 
                'min_days' => 8,
                'next' => 'h-1_bulan'
            ],
            'h-1_minggu' => [
                'type' => 'h-1_minggu', 
                'statuses' => ['pending', 'h-2_bulan', 'h-1_bulan'], 
                'max_days' => 7, 
                'min_days' => 1,
                'next' => 'h-1_minggu'
            ],
        ];

        foreach ($reminders as $config) {
            $maxDate = (clone $today)->addDays($config['max_days'])->toDateString();
            $minDate = (clone $today)->addDays($config['min_days'])->toDateString();

            McuMasterData::whereIn('notification_status', $config['statuses'])
                ->whereNotNull('mcu_date')
                ->whereDate('mcu_date', '>=', $minDate)
                ->whereDate('mcu_date', '<=', $maxDate)
                ->chunkById(100, function ($participants) use ($config) {
                    foreach ($participants as $participant) {
                        $this->sendNotifications($participant, $config['type'], $config['next']);
                    }
                });
        }

        $this->info('Proses reminder MCU tahunan berhasil dijalankan.');
    }

    private function sendNotifications(McuMasterData $participant, string $type, string $nextStatus)
    {
        // Karyawan menerima WhatsApp (karena tidak ada email di McuMasterData)
        if (!empty($participant->hp_number)) {
            try {
                $participant->notifyNow(new McuReminderNotification($participant, $type, [\App\Channels\WhatsAppChannel::class]));
            } catch (\Exception $e) {
                Log::error("MCU Reminder WA Error: " . $e->getMessage());
            }
        }

        $participant->update(['notification_status' => $nextStatus]);
    }
}
