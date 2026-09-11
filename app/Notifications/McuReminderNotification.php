<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Models\McuMasterData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class McuReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public ?array $channels;

    public function __construct(
        public McuMasterData $participant,
        public string $type, 
        ?array $channels = null
    ) {
        $this->channels = $channels;
    }

    public function via(object $notifiable): array
    {
        if ($this->channels !== null) {
            return $this->channels;
        }

        // Karyawan hanya menerima WA
        return [WhatsAppChannel::class];
    }

    public function toWhatsApp(object $notifiable): array
    {
        $date = $this->participant->mcu_date ? Carbon::parse($this->participant->mcu_date)->format('d M Y') : '-';
        $employeeName = $this->participant->employee_name ?? 'Karyawan';

        $phone = method_exists($notifiable, 'routeNotificationFor') 
            ? $notifiable->routeNotificationFor('whatsapp') 
            : null;

        if (!$phone) {
            $phone = $this->participant->hp_number;
        }

        $text  = "*PEMBERITAHUAN JADWAL MCU TAHUNAN*\n\n";
        $text .= "Halo {$employeeName},\n";
        $text .= "Anda telah dijadwalkan untuk Medical Check-Up (MCU) pada tanggal *{$date}*.\n";
        $text .= "Mohon persiapkan diri Anda.";

        return [
            'phone' => $phone,
            'message' => $text
        ];
    }
}
