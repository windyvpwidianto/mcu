<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\McuRecord;
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

        // 1. Cek jika mcu_date sudah lewat (dihapus karena 'overdue' tidak ada di ENUM notification_status)

        // 2. Peta eskalasi aturan reminder
        $reminders = [
            'h-2_bulan' => [
                'type' => 'h-2_bulan', 
                'statuses' => ['pending'], 
                'max_days' => 60, 
                'min_days' => 31,
                'next' => 'reminder_1'
            ],
            'h-1_bulan' => [
                'type' => 'h-1_bulan', 
                'statuses' => ['pending', 'reminder_1'], 
                'max_days' => 30, 
                'min_days' => 8,
                'next' => 'reminder_2'
            ],
            'h-1_minggu' => [
                'type' => 'h-1_minggu', 
                'statuses' => ['pending', 'reminder_1', 'reminder_2'], 
                'max_days' => 7, 
                'min_days' => 1,
                'next' => 'final_reminder'
            ],
        ];

        foreach ($reminders as $config) {
            $maxDate = (clone $today)->addDays($config['max_days'])->toDateString();
            $minDate = (clone $today)->addDays($config['min_days'])->toDateString();

            McuRecord::whereIn('notification_status', $config['statuses'])
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

    private function sendNotifications(McuRecord $participant, string $type, string $nextStatus)
    {
        // Karyawan menerima WhatsApp (karena tidak ada email di McuMasterData)
        if (!empty($participant->employee->phone_number)) {
            try {
                $participant->employee->notifyNow(new McuReminderNotification($participant, $type, [\App\Channels\WhatsAppChannel::class]));
            } catch (\Exception $e) {
                Log::error("MCU Reminder WA Error: " . $e->getMessage());
            }
        }

        $participant->update(['notification_status' => $nextStatus]);
    }
}
