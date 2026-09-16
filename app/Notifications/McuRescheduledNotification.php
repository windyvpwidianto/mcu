<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Models\McuRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class McuRescheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public McuRecord $newRecord,
        public ?array $channels = null
    ) {
    }

    public function via(object $notifiable): array
    {
        if ($this->channels !== null) {
            return $this->channels;
        }

        // Check if contractor
        if (isset($notifiable->contractors) && $notifiable->contractors->isNotEmpty()) {
            return ['mail'];
        }

        return [WhatsAppChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $date = $this->newRecord->mcu_date ? Carbon::parse($this->newRecord->mcu_date)->translatedFormat('d F Y') : '-';
        $employeeName = $notifiable->name ?? 'Karyawan';

        return (new MailMessage)
                    ->subject('Pemberitahuan Perubahan Jadwal MCU')
                    ->greeting('Halo,')
                    ->line("Jadwal Medical Check-Up (MCU) untuk karyawan atas nama {$employeeName} telah diubah (Rescheduled).")
                    ->line("Jadwal MCU yang baru adalah: {$date}.")
                    ->line('Mohon agar dapat menginformasikan kepada karyawan yang bersangkutan untuk mempersiapkan diri.')
                    ->line('Terima kasih.');
    }

    public function toWhatsApp(object $notifiable): array
    {
        $date = $this->newRecord->mcu_date ? Carbon::parse($this->newRecord->mcu_date)->translatedFormat('d F Y') : '-';
        $employeeName = $notifiable->name ?? 'Karyawan';

        $phone = method_exists($notifiable, 'routeNotificationFor') 
            ? $notifiable->routeNotificationFor('whatsapp') 
            : null;

        // If no explicit route, fallback to maybe hp_number or whatsapp_number if available on user
        if (!$phone && isset($notifiable->whatsapp_number)) {
            $phone = $notifiable->whatsapp_number;
        }

        $text  = "*PEMBERITAHUAN PERUBAHAN JADWAL MCU*\n\n";
        $text .= "Halo {$employeeName},\n";
        $text .= "Jadwal Medical Check-Up (MCU) Anda telah diubah (Rescheduled).\n";
        $text .= "Jadwal MCU Anda yang baru adalah pada tanggal *{$date}*.\n";
        $text .= "Mohon persiapkan diri Anda.";

        return [
            'phone' => $phone,
            'message' => $text
        ];
    }
}


