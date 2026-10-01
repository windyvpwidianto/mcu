<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\McuRecord;
use App\Models\McuResult;
use App\Notifications\McuReminderNotification;
use App\Notifications\McuResultNotification;
use Illuminate\Support\Facades\Log;

class BlastMcuNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mcu:blast-notifications {type : "schedule" or "result"} {--year= : Tahun MCU (default tahun ini)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim blast notifikasi jadwal MCU atau hasil MCU secara massal ke semua karyawan terkait';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $year = $this->option('year') ?: date('Y');

        if (!in_array($type, ['schedule', 'result'])) {
            $this->error('Tipe tidak valid. Gunakan "schedule" atau "result".');
            return;
        }

        if ($type === 'schedule') {
            $this->blastSchedules($year);
        } else {
            $this->blastResults($year);
        }
    }

    private function blastSchedules($year)
    {
        $this->info("Memulai blast notifikasi Jadwal MCU untuk tahun {$year}...");

        $records = McuRecord::where('mcu_year', $year)
            ->whereNotNull('mcu_date')
            ->whereIn('notification_status', ['pending', 'reminder_1', 'reminder_2'])
            ->get();

        if ($records->isEmpty()) {
            $this->info('Tidak ada jadwal MCU yang perlu dikirimkan notifikasinya.');
            return;
        }

        $count = 0;
        foreach ($records as $record) {
            if (!empty($record->employee->phone_number)) {
                try {
                    $record->employee->notifyNow(new McuReminderNotification($record, 'h-1_minggu', [\App\Channels\WhatsAppChannel::class]));
                    $count++;
                    
                    // Kita bisa update status ke final_reminder
                    $record->update(['notification_status' => 'final_reminder']);
                } catch (\Exception $e) {
                    Log::error("Gagal blast jadwal MCU ke {$record->employee->name}: " . $e->getMessage());
                }
            }
        }

        $this->info("Selesai. Berhasil mengirim jadwal MCU ke {$count} karyawan.");
    }

    private function blastResults($year)
    {
        $this->info("Memulai blast notifikasi Hasil MCU untuk tahun {$year}...");

        $results = McuResult::whereHas('record', function ($q) use ($year) {
                $q->where('mcu_year', $year);
            })
            ->whereNotNull('status') // hanya yang sudah ada hasilnya
            ->get();

        if ($results->isEmpty()) {
            $this->info('Tidak ada hasil MCU untuk di-blast.');
            return;
        }

        $count = 0;
        foreach ($results as $result) {
            $employee = $result->record->employee ?? null;
            if ($employee && !empty($employee->phone_number)) {
                try {
                    $employee->notifyNow(new McuResultNotification($result, 'employee', [\App\Channels\WhatsAppChannel::class]));
                    $count++;
                } catch (\Exception $e) {
                    Log::error("Gagal blast hasil MCU ke {$employee->name}: " . $e->getMessage());
                }
            }
        }

        $this->info("Selesai. Berhasil mengirim hasil MCU ke {$count} karyawan.");
    }
}
