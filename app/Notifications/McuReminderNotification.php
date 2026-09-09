<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Models\McuParticipant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\AnonymousNotifiable;

class McuReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public ?array $channels;

    public function __construct(
        public McuParticipant $participant,
        public string $type, // 'h-30', 'h-14', 'h-7', 'h-1', 'missed', 'new_schedule', 'new_schedule_spv', 'new_schedule_dept_head'
        ?array $channels = null
    ) {
        $this->channels = $channels;
    }

    public function via(object $notifiable): array
    {
        if ($this->channels !== null) {
            return $this->channels;
        }
        // 1. Jika ini Dept Head (cek via tipe atau jika ID notifiable adalah ID Dept Head dari relasi), HANYA VIA EMAIL
        $isDeptHead = $this->type === 'new_schedule_dept_head'
            || (isset($notifiable->id) && $notifiable->id === $this->participant->employee?->department?->head?->id);

        if ($isDeptHead) {
            return ['mail'];
        }

        // Jika input manual (AnonymousNotifiable), hanya kirim via WA karena tidak ada data email
        if ($notifiable instanceof AnonymousNotifiable) {
            return [WhatsAppChannel::class];
        }

        // Default untuk karyawan dan SPV sistem (Kirim Email & WA)
        return ['mail', WhatsAppChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Gunakan Null-Safe Operator (?->) untuk mencegah N+1 & Crash di Queue Worker
        $date = $this->participant->schedule?->schedule_date?->format('d M Y') ?? 'Tanggal belum ditentukan';
        $employeeName = $this->participant->employee?->name ?? 'Karyawan';
        $location = $this->participant->schedule?->location ?? '-';

        $message = new MailMessage;
        $message->greeting("Halo {$notifiable->name},");

        // 2. TEMPLATE UNTUK ATASAN (SPV & DEPT HEAD) - AGAR TIDAK SALAH PAHAM
        if ($this->isManager($notifiable)) {
            return $message
                ->subject("Pemberitahuan Jadwal MCU Anggota Tim: {$employeeName}")
                ->line("Berikut adalah pemberitahuan bahwa anggota tim/departemen Anda telah dijadwalkan untuk mengikuti Medical Check-Up (MCU):")
                ->line("Nama Karyawan: **{$employeeName}**")
                ->line("Tanggal MCU: **{$date}**")
                ->line("Lokasi: **{$location}**")
                ->action('Lihat Detail Jadwal', url('/mcu/detail/' . $this->participant->id))
                ->line("Mohon bantuannya untuk koordinasi operasional dan memastikan kehadiran yang bersangkutan.");
        }

        // 3. TEMPLATE UNTUK KARYAWAN YANG BERSANGKUTAN
        $message->subject("Pengingat Jadwal Medical Check-Up (MCU)")
            ->line("Ini adalah pengingat bahwa Anda memiliki jadwal MCU pada tanggal **{$date}** di **{$location}**.");

        if ($this->type === 'missed') {
            $message->subject("Pemberitahuan: Tidak Hadir MCU")
                ->line("Sistem mencatat Anda tidak hadir pada jadwal MCU yang telah ditentukan sebelumnya.");
        }

        return $message->action('Lihat Detail', url('/mcu/detail/' . $this->participant->id));
    }
    public function toWhatsApp(object $notifiable): array
    {
        $date = $this->participant->schedule?->schedule_date?->format('d M Y') ?? '-';
        $employeeName = $this->participant->employee?->name ?? 'Karyawan';

        // 1. Coba ambil dari routeNotificationFor terlebih dahulu (Standar Laravel)
        $phone = method_exists($notifiable, 'routeNotificationFor') 
            ? $notifiable->routeNotificationFor('whatsapp') 
            : null;

        // Cek apakah penerima adalah Atasan (Supervisor / Dept Head)
        if ($this->isManager($notifiable)) {
            $spvName = $notifiable->name ?? 'Bapak/Ibu Atasan';

            $text  = "*PEMBERITAHUAN JADWAL MCU BAWAHAN*\n\n";
            $text .= "Halo {$spvName},\n";
            $text .= "Anggota tim Anda, *{$employeeName}*, dijadwalkan untuk Medical Check-Up pada tanggal *{$date}*.\n";
            $text .= "Mohon bantuan Anda untuk memastikan kehadirannya.";

            // 2. Fallback: Ambil dari tabel MCU Participant / properti User
            if (!$phone) {
                $phone = $this->participant->spv_wa_number
                    ?? $notifiable->whatsapp_number
                    ?? $notifiable->phone;
            }
        } else {
            $text  = "*PEMBERITAHUAN JADWAL MCU*\n\n";
            $text .= "Halo {$notifiable->name},\n";
            $text .= "Anda telah dijadwalkan untuk Medical Check-Up pada tanggal *{$date}*.\n";
            $text .= "Mohon persiapkan diri Anda.";

            // 2. Fallback: Ambil dari tabel MCU Participant / properti User
            if (!$phone) {
                $phone = $this->participant->whatsapp_number
                    ?? $notifiable->whatsapp_number
                    ?? $notifiable->phone;
            }
        }

        return [
            'phone' => $phone,
            'message' => $text
        ];
    }

    /**
     * Helper untuk mendeteksi apakah penerima notifikasi adalah Atasan
     */
    private function isManager(object $notifiable): bool
    {
        if ($notifiable instanceof \Illuminate\Notifications\AnonymousNotifiable) {
            return true;
        }

        if (in_array($this->type, ['new_schedule_spv', 'new_schedule_dept_head'])) {
            return true;
        }

        // Jika ID penerima cocok dengan supervisor_id atau dept_head_id di tabel participant, berarti dia Atasan!
        if (isset($notifiable->id)) {
            if ($notifiable->id === $this->participant->supervisor_id || $notifiable->id === $this->participant->dept_head_id) {
                return true;
            }

            // Fallback: Jika ID-nya bukan ID karyawan peserta MCU
            return $notifiable->id !== $this->participant->employee_id;
        }

        return false;
    }
}
