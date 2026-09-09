<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\McuParticipant;
use App\Notifications\McuReminderNotification;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class ProcessMcuReminders extends Command
{
    protected $signature = 'mcu:process-reminders';
    protected $description = 'Proses notifikasi h-30, h-14, h-7, h-1 untuk MCU';

    public function handle()
    {
        // 1. Peta eskalasi aturan reminder dengan rentang hari untuk mencegah rantai terputus jika cron mati 1 hari
        $reminders = [
            'h-30' => [
                'type' => 'h-30', 
                'statuses' => ['pending'], 
                'max_days' => 30, 
                'min_days' => 15,
                'next' => 'notified'
            ],
            'h-14' => [
                'type' => 'h-14', 
                'statuses' => ['pending', 'notified'], 
                'max_days' => 14, 
                'min_days' => 8,
                'next' => 'reminder_1'
            ],
            'h-7' => [
                'type' => 'h-7', 
                'statuses' => ['pending', 'notified', 'reminder_1'], 
                'max_days' => 7, 
                'min_days' => 2,
                'next' => 'reminder_2'
            ],
            'h-1' => [
                'type' => 'h-1', 
                'statuses' => ['pending', 'notified', 'reminder_1', 'reminder_2'], 
                'max_days' => 1, 
                'min_days' => 1,
                'next' => 'final_reminder'
            ],
        ];

        $today = Carbon::today();

        foreach ($reminders as $config) {
            $maxDate = (clone $today)->addDays($config['max_days'])->toDateString();
            $minDate = (clone $today)->addDays($config['min_days'])->toDateString();

            // 2. Filter menggunakan rentang (>= min_days dan <= max_days)
            McuParticipant::with(['schedule', 'employee', 'supervisor', 'deptHead'])
                ->whereIn('notification_status', $config['statuses'])
                ->whereHas('schedule', function ($q) use ($minDate, $maxDate) {
                    $q->whereDate('schedule_date', '>=', $minDate)
                      ->whereDate('schedule_date', '<=', $maxDate);
                })
                ->chunkById(100, function ($participants) use ($config) {
                    foreach ($participants as $participant) {
                        $this->sendNotifications($participant, $config['type'], $config['next']);
                    }
                });
        }

        $this->info('Proses reminder MCU berhasil dijalankan.');
    }

    // 2. Ubah pemanggilan variabel di dalam sendNotifications():
    private function sendNotifications(McuParticipant $participant, string $type, string $nextStatus)
    {
        $employee = $participant->employee;

        // AMBIL LANGSUNG DARI PARTICIPANT SESAAT SETELAH DI-EAGER LOAD
        $supervisor = $participant->supervisor;

        // Jika kolom dept_head_id ada di tabel, gunakan $participant->deptHead
        // Jika tidak, fallback ke relasi departemen karyawan
        $departmentHead = $participant->deptHead ?? $employee?->department?->head;

        $recipients = collect([
            ['user' => $employee, 'role' => 'employee'],
        ]);

        if (in_array($type, ['h-30', 'h-14', 'h-7', 'h-1']) && $departmentHead) {
            $recipients->push(['user' => $departmentHead, 'role' => 'dept_head']);
        }

        if (in_array($type, ['h-7', 'h-1']) && $supervisor) {
            $recipients->push(['user' => $supervisor, 'role' => 'supervisor']);
        }

        // Filter null & pastikan setiap user ID hanya dikirim 1 kali dengan role tertinggi
        $validRecipients = $recipients->filter(fn($item) => !is_null($item['user']))
                                      ->unique(fn($item) => $item['user']->id);

        foreach ($validRecipients as $recipient) {
            $user = $recipient['user'];
            $role = $recipient['role'];
            
            // Dept Head hanya menerima Email, Karyawan dan SPV menerima Email & WA
            if ($role === 'dept_head') {
                try {
                    $user->notify(new McuReminderNotification($participant, $type, ['mail']));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('MCU Reminder Command Email Error (Dept Head): ' . $e->getMessage());
                }
            } else {
                // Kirim WhatsApp langsung tanpa masuk antrean
                $user->notifyNow(new McuReminderNotification($participant, $type, [\App\Channels\WhatsAppChannel::class]));
                
                // Kirim Email dengan try-catch agar error SMTP tidak membuat proses crash
                try {
                    $user->notify(new McuReminderNotification($participant, $type, ['mail']));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("MCU Reminder Command Email Error ({$role}): " . $e->getMessage());
                }
            }
        }

        $participant->update(['notification_status' => $nextStatus]);
    }
}
