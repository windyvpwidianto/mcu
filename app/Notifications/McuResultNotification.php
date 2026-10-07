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
        $this->result->loadMissing(['record.employee', 'record.supervisor', 'diseaseCategories']);

        $employeeName = $this->result->record->employee->name ?? 'Karyawan';
        $supervisorName = $this->result->record->supervisor->name ?? 'Supervisor';
        $statusText = str_replace('_', ' ', strtoupper($this->result->status ?? ''));
        $specialistType = $this->result->specialist_type ?? 'Dokter Spesialis';
        $followUpDate = $this->result->follow_up_date ? \Carbon\Carbon::parse($this->result->follow_up_date)->translatedFormat('d F Y') : '-';
        $cleanNotes = !empty($this->result->doctor_notes) ? trim(strip_tags($this->result->doctor_notes)) : '';
        $diseases = $this->result->diseaseCategories->pluck('name')->join(', ');

        if ($this->recipientType === 'employee') {
            $mail = (new MailMessage)
                ->subject('Hasil Review Medical Check-Up (MCU): ' . $statusText)
                ->greeting('Halo Bapak/Ibu ' . $employeeName . ',');

            if ($this->result->status === 'fit_to_work') {
                $mail->line('Hasil Medical Check Up (MCU) Anda telah selesai direview oleh Dokter Onsite dengan status FIT TO WORK.')
                    ->line('Anda dinyatakan sehat dan layak bekerja tanpa pembatasan. Tetap jaga kesehatan dengan menerapkan pola hidup sehat dan prosedur keselamatan kerja.')
                    ->action('Unduh Sertifikat Fit to Work', route('mcu.fit-letter', $this->result->id))
                    ->line('Terima kasih dan semoga selalu sehat.');
            } elseif ($this->result->status === 'fit_with_notes') {
                $mail->line('Hasil Medical Check Up (MCU) Anda telah selesai direview oleh Dokter Onsite dengan status FIT WITH NOTE.')
                    ->line('Anda tetap dinyatakan layak bekerja dengan catatan/batasan tertentu.')
                    ->line('**Catatan Dokter:** ' . ($cleanNotes ?: 'Ikuti anjuran dokter onsite'))
                    ->action('Unduh Sertifikat Fit with Notes', route('mcu.fit-letter', $this->result->id))
                    ->line('Terima kasih dan semoga selalu sehat.');
            } elseif ($this->result->status === 'temporary_unfit') {
                $mail->line('Hasil Medical Check Up (MCU) Anda telah kami terima. Berdasarkan hasil evaluasi Dokter Onsite, status kesehatan Anda saat ini adalah TEMPORARY UNFIT (Tidak Fit Sementara).')
                    ->line('**Rujukan Dokter Spesialis:** ' . $specialistType)
                    ->line('**Batas Waktu Pemeriksaan:** ' . $followUpDate);

                if ($diseases) {
                    $mail->line('**Temuan Diagnosa:** ' . $diseases);
                }
                if ($cleanNotes) {
                    $mail->line('**Catatan Dokter Onsite:** ' . $cleanNotes);
                }

                $mail->line('Mohon segera membawa Formulir Surat Rujukan Medis resmi (TT-OHS-FRO-028D) ke dokter spesialis/faskes yang dituju sebelum batas tanggal di atas.')
                    ->line('Setelah pemeriksaan selesai, serahkan berkas resume medis spesialis ke Klinik Onsite Toka Tindung Project untuk re-evaluasi kelaikan bekerja (Fit to Work).')
                    ->action('Unduh Formulir Rujukan (TT-OHS-FRO-028D)', route('mcu.referral-letter', $this->result->id))
                    ->line('Terima kasih atas kerja samanya.');
            } else {
                $mail->line('Hasil Medical Check Up (MCU) Anda telah kami terima. Berdasarkan hasil pemeriksaan, status kesehatan Anda adalah ' . $statusText . '.')
                    ->line('Terima kasih atas kerja samanya.');
            }

            return $mail;
        }

        // Template Email Untuk Dept Head / Supervisor
        $mailSpv = (new MailMessage)
            ->subject('Pemberitahuan Status MCU Anggota Tim: ' . $employeeName . ' (' . $statusText . ')')
            ->greeting('Halo, ' . ($notifiable->name ?? 'Supervisor'))
            ->line('Pemberitahuan bahwa proses review medis untuk anggota tim Anda telah selesai dilakukan oleh dokter onsite.')
            ->line('**Nama Karyawan:** ' . $employeeName)
            ->line('**Status Kebugaran Kerja:** ' . $statusText);

        if ($this->result->status === 'temporary_unfit') {
            $mailSpv->line('**Rujukan Spesialis:** ' . $specialistType)
                ->line('**Target Selesai Pemeriksaan:** ' . $followUpDate)
                ->line('Karyawan sedang dalam masa rujukan dan memerlukan pemeriksaan penunjang sebelum evaluasi akhir kelaikan kerja.')
                ->action('Buka Menu Monitoring Dokter', route('mcu.doctor-review'));
        } elseif ($this->result->status === 'fit_to_work') {
            $mailSpv->line('Karyawan dinyatakan sehat dan layak bekerja tanpa pembatasan. Sertifikat kelaikan kerja telah diterbitkan.')
                ->action('Buka Menu Monitoring', route('mcu.doctor-review'));
        } else {
            $mailSpv->action('Buka Menu Monitoring', route('mcu.doctor-review'));
        }

        return $mailSpv;
    }

    /**
     * Method custom untuk pengiriman pesan WhatsApp via WhatsAppChannel.
     */
    public function toWhatsApp(object $notifiable): array
    {
        // 1. PENTING: Tambahkan 'diseaseCategories' ke dalam loadMissing
        $this->result->loadMissing(['record.employee', 'record.supervisor', 'diseaseCategories']);

        $employeeName = $this->result->record->employee->name ?? 'Karyawan';
        $statusText = str_replace('_', ' ', strtoupper($this->result->status ?? ''));
        $specialistType = $this->result->specialist_type ?? 'Dokter Spesialis';
        $followUpDate = $this->result->follow_up_date ? \Carbon\Carbon::parse($this->result->follow_up_date)->translatedFormat('d F Y') : '-';
        $cleanNotes = !empty($this->result->doctor_notes) ? trim(strip_tags($this->result->doctor_notes)) : '';
        $diseases = $this->result->diseaseCategories->pluck('name')->join(', ');

        if ($this->recipientType === 'employee') {
            if ($this->result->status === 'fit_to_work') {
                $text = "Halo Bapak/Ibu {$employeeName},\n\n";
                $text .= "Hasil Medical Check Up (MCU) Anda telah direview oleh Dokter Onsite dengan status: *FIT TO WORK*.\n\n";
                $text .= "Anda dinyatakan sehat dan layak bekerja tanpa pembatasan. Tetap jaga kesehatan dan terapkan pola hidup sehat.\n\n";
                $text .= "📄 *Unduh Sertifikat Kelaikan Kerja:*\n" . route('mcu.fit-letter', $this->result->id) . "\n\n";
                $text .= "Terima kasih dan semoga selalu sehat.";
            } elseif ($this->result->status === 'fit_with_notes') {
                $text = "Halo Bapak/Ibu {$employeeName},\n\n";
                $text .= "Hasil Medical Check Up (MCU) Anda telah direview dengan status: *FIT WITH NOTE*.\n\n";
                $text .= "Anda tetap dinyatakan layak bekerja dengan catatan/rekomendasi dokter.\n";
                if ($cleanNotes) {
                    $text .= "*Catatan Dokter:* {$cleanNotes}\n";
                }
                $text .= "\n📄 *Unduh Sertifikat:* " . route('mcu.fit-letter', $this->result->id) . "\n\n";
                $text .= "Terima kasih dan selalu patuhi anjuran kesehatan.";
            } elseif ($this->result->status === 'temporary_unfit') {
                $text = "Halo Bapak/Ibu {$employeeName},\n\n";
                $text .= "Berdasarkan hasil evaluasi MCU oleh Dokter Onsite, status kesehatan Anda saat ini adalah:\n";
                $text .= "⚠️ *TEMPORARY UNFIT* (Tidak Fit Sementara)\n\n";
                $text .= "*Informasi Rujukan Dokter Spesialis:*\n";
                $text .= "• *Spesialis Tujuan:* {$specialistType}\n";
                $text .= "• *Batas Tanggal Konsultasi:* {$followUpDate}\n";
                if ($diseases) {
                    $text .= "• *Diagnosa Temuan:* {$diseases}\n";
                }
                if ($cleanNotes) {
                    $text .= "• *Catatan/Anjuran Dokter:* {$cleanNotes}\n";
                }
                $text .= "\n📋 *Surat Pengantar Rujukan (TT-OHS-FRO-028D):*\n";
                $text .= "Silakan ambil surat rujukan fisik di Klinik Onsite Toka Tindung Project atau unduh via tautan:\n";
                $text .= route('mcu.referral-letter', $this->result->id) . "\n\n";
                $text .= "Setelah selesai berkonsultasi di dokter spesialis, mohon segera serahkan surat resume medis ke Klinik Onsite agar Dokter Site dapat melakukan re-evaluasi kelaikan kerja (*Fit to Work*).\n\n";
                $text .= "Terima kasih atas kerja samanya.";
            } else {
                $text = "Halo Bapak/Ibu {$employeeName},\n\n";
                $text .= "Hasil Medical Check Up (MCU) Anda telah kami terima. Berdasarkan hasil pemeriksaan, status kesehatan Anda adalah *{$statusText}*.\n\n";
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
            $text .= "Proses review medis untuk anggota tim Anda telah selesai dilakukan oleh dokter onsite.\n\n";
            $text .= "*Nama Karyawan:* {$employeeName}\n";
            $text .= "*Status Kebugaran Kerja:* *{$statusText}*\n";

            if ($this->result->status === 'temporary_unfit') {
                $text .= "• *Rujukan Spesialis:* {$specialistType}\n";
                $text .= "• *Batas Tanggal:* {$followUpDate}\n";
                if ($diseases) {
                    $text .= "• *Diagnosa Temuan:* {$diseases}\n";
                }
                $text .= "\nKaryawan diarahkan untuk melakukan pemeriksaan spesialis sebelum re-evaluasi kelaikan kerja akhir.\n";
            } elseif ($diseases) {
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
