<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Models\McuResult;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class McuResultNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $result;
    protected $recipientType;
    protected $channels;

    public function __construct(McuResult $result, string $recipientType, array $channels = ['mail', 'database', \App\Channels\WhatsAppChannel::class])
    {
        $this->result = $result;
        $this->recipientType = $recipientType;
        $this->channels = $channels;
    }

    /**
     * Tentukan channel pengiriman berdasarkan target penerima.
     */
    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    /**
     * Format email untuk masing-masing penerima.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // PENTING: Ambil ulang relasi yang hilang akibat serialisasi Queue
        $this->result->loadMissing(['record.employee', 'record.supervisor']);

        $employeeName = $this->result->record->employee->name ?? 'Karyawan';
        $supervisorName = $this->result->record->supervisor->name ?? 'Supervisor';
        $statusText = str_replace('_', ' ', strtoupper($this->result->status));

        if ($this->recipientType === 'employee') {
            $mail = (new MailMessage)
                ->subject('Hasil Review Medical Check-Up (MCU) Anda')
                ->greeting('Halo Bapak/Ibu ' . $employeeName . ',');

            if ($this->result->status === 'fit_to_work') {
                $mail->line('Hasil Medical Check Up (MCU) Anda telah kami terima. Berdasarkan hasil pemeriksaan, status kesehatan Anda adalah FIT TO WORK.')
                    ->line('Anda dinyatakan layak bekerja tanpa pembatasan. Tetap jaga kesehatan dengan menerapkan pola hidup sehat.')
                    ->line('Terima kasih dan semoga selalu sehat.');
            } elseif ($this->result->status === 'fit_with_notes') {
                $mail->line('Hasil Medical Check Up (MCU) Anda telah kami terima. Berdasarkan hasil pemeriksaan, status kesehatan Anda adalah FIT WITH NOTE.')
                    ->line('Anda tetap dinyatakan layak bekerja. Namun, kami menyarankan Anda untuk melakukan konsultasi dengan Dokter Onsite agar hasil pemeriksaan dapat dijelaskan lebih lanjut dan saran untuk tindak lanjut yang sesuai.')
                    ->line('Terima kasih dan semoga selalu sehat.');
            } elseif ($this->result->status === 'temporary_unfit') {
                $mail->line('Hasil Medical Check Up (MCU) Anda telah kami terima. Berdasarkan hasil pemeriksaan, status kesehatan Anda adalah TEMPORARY UNFIT.')
                    ->line('Mohon segera melakukan konsultasi dengan Dokter Onsite untuk mendapatkan surat rujukan.')
                    ->line('Setelah konsultasi selesai, mohon menyerahkan hasil pemeriksaan kepada Klinik Perusahaan sebagai dasar evaluasi status kesehatan dan kelayakan bekerja.')
                    ->line('Apabila memerlukan bantuan atau informasi lebih lanjut, silakan menghubungi Klinik Toka.')
                    ->line('Terima kasih atas kerja samanya.');
            } else {
                $mail->line('Hasil Medical Check Up (MCU) Anda telah kami terima. Berdasarkan hasil pemeriksaan, status kesehatan Anda adalah ' . $statusText . '.')
                    ->line('Terima kasih atas kerja samanya.');
            }

            $mail->action('Lihat Detail Hasil', url('/dashboard/mcu'));
            return $mail;
        }

        // Template Email Untuk Dept Head / Supervisor
        return (new MailMessage)
            ->subject('Pemberitahuan Status MCU Anggota Tim: ' . $employeeName)
            ->greeting('Halo, ' . ($notifiable->name ?? 'Supervisor'))
            ->line('Pemberitahuan bahwa proses review medis untuk anggota tim Anda telah selesai dilakukan oleh dokter.')
            ->line('**Nama Karyawan:** ' . $employeeName)
            ->line('**Status Kebugaran Kerja:** ' . $statusText)
            ->action('Buka Menu Monitoring', url('/supervisor/mcu-monitoring'));
    }

    /**
     * Method custom untuk pengiriman pesan WhatsApp via WhatsAppChannel.
     */
    public function toWhatsApp(object $notifiable): array
    {
        // 1. PENTING: Tambahkan 'diseaseCategories' ke dalam loadMissing
        $this->result->loadMissing(['record.employee', 'record.supervisor', 'diseaseCategories']);

        $employeeName = $this->result->record->employee->name ?? 'Karyawan';
        $statusText = str_replace('_', ' ', strtoupper($this->result->status));

        // 2. Ambil daftar penyakit dari tabel pivot dan gabungkan menjadi string teks
        $diseases = $this->result->diseaseCategories->pluck('name')->join(', ');

        if ($this->recipientType === 'employee') {
            if ($this->result->status === 'fit_to_work') {
                $text = "Halo Bapak/Ibu {$employeeName},\n\n";
                $text .= "Hasil Medical Check Up (MCU) Anda telah kami terima. Berdasarkan hasil pemeriksaan, status kesehatan Anda adalah FIT TO WORK.\n\n";
                $text .= "Anda dinyatakan layak bekerja tanpa pembatasan. Tetap jaga kesehatan dengan menerapkan pola hidup sehat.\n\n";
                $text .= "Terima kasih dan semoga selalu sehat.";
            } elseif ($this->result->status === 'fit_with_notes') {
                $text = "Halo Bapak/Ibu {$employeeName},\n\n";
                $text .= "Hasil Medical Check Up (MCU) Anda telah kami terima. Berdasarkan hasil pemeriksaan, status kesehatan Anda adalah FIT WITH NOTE.\n\n";
                $text .= "Anda tetap dinyatakan layak bekerja. Namun, kami menyarankan Anda untuk melakukan konsultasi dengan Dokter Onsite agar hasil pemeriksaan dapat dijelaskan lebih lanjut dan saran untuk tindak lanjut yang sesuai.\n\n";
                $text .= "Terima kasih dan semoga selalu sehat.";
            } elseif ($this->result->status === 'temporary_unfit') {
                $text = "Halo Bapak/Ibu {$employeeName},\n\n";
                $text .= "Hasil Medical Check Up (MCU) Anda telah kami terima. Berdasarkan hasil pemeriksaan, status kesehatan Anda adalah TEMPORARY UNFIT.\n\n";
                $text .= "Mohon segera melakukan konsultasi dengan Dokter Onsite untuk mendapatkan surat rujukan. \n";
                $text .= "Setelah konsultasi selesai, mohon menyerahkan hasil pemeriksaan kepada Klinik Perusahaan sebagai dasar evaluasi status kesehatan dan kelayakan bekerja.\n\n";
                $text .= "Apabila memerlukan bantuan atau informasi lebih lanjut, silakan menghubungi Klinik Toka.\n\n";
                $text .= "Terima kasih atas kerja samanya.";
            } else {
                $text = "Halo Bapak/Ibu {$employeeName},\n\n";
                $text .= "Hasil Medical Check Up (MCU) Anda telah kami terima. Berdasarkan hasil pemeriksaan, status kesehatan Anda adalah {$statusText}.\n\n";
                if ($diseases) {
                    $text .= "*Temuan Medis / Penyakit:* {$diseases}\n\n";
                }
                $text .= "Terima kasih atas kerja samanya.";
            }

            $phone = $notifiable->phone_number ?? $notifiable->whatsapp_number ?? $notifiable->phone ?? $this->result->record->whatsapp_number;
        } else {
            $spvName = $notifiable->name ?? 'Bapak/Ibu Atasan';

            $text = "*PEMBERITAHUAN HASIL MCU ANGGOTA TIM*\n\n";
            $text .= "Halo {$spvName},\n";
            $text .= "Proses review medis untuk anggota tim Anda telah selesai dilakukan oleh dokter.\n\n";
            $text .= "*Nama Karyawan:* {$employeeName}\n";
            $text .= "*Status Kebugaran Kerja:* {$statusText}\n";

            if ($diseases) {
                $text .= "*Temuan Medis / Penyakit:* {$diseases}\n";
            }

            $phone = $notifiable->phone_number ?? $notifiable->whatsapp_number ?? $notifiable->phone ?? $this->result->record->spv_wa_number;
        }

        return [
            'phone'   => $phone,
            'message' => $text
        ];
    }

    public function toArray(object $notifiable): array
    {
        $statusLabel = match($this->result->status) {
            'fit_to_work' => 'FIT TO WORK',
            'fit_with_notes' => 'FIT WITH NOTE',
            'temporary_unfit' => 'TEMPORARY UNFIT',
            default => str_replace('_', ' ', strtoupper($this->result->status ?? ''))
        };

        return [
            'mcu_result_id' => $this->result->id,
            'status'        => $this->result->status,
            'message'       => $this->recipientType === 'employee'
                ? "Hasil review MCU Anda sudah keluar dengan status {$statusLabel}"
                : "Anggota tim Anda (" . ($this->result->record->employee->name ?? 'Karyawan') . ") telah di-review dengan status {$statusLabel}",
        ];
    }
}
