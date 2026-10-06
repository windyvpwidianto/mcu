<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Models\McuRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class McuReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public ?array $channels;

    public function __construct(
        public McuRecord $participant,
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
        $date = $this->participant->mcu_date ? Carbon::parse($this->participant->mcu_date)->translatedFormat('d F Y') : '-';
        $employeeName = $this->participant->employee->name ?? 'Karyawan';

        $phone = method_exists($notifiable, 'routeNotificationFor') 
            ? $notifiable->routeNotificationFor('whatsapp') 
            : null;

        if (!$phone) {
            $phone = $this->participant->employee->phone_number ?? '';
        }

        if (in_array($this->type, ['expired', 'overdue'])) {
            $formattedDate = $this->participant->mcu_date ? Carbon::parse($this->participant->mcu_date)->translatedFormat('d F Y') : '-';
            $text = "Yth. Bapak/Ibu {$employeeName},\n\n";
            $text .= "Kami informasikan bahwa masa berlaku Medical Check Up (MCU) Anda telah berakhir pada tanggal {$formattedDate}.\n\n";
            $text .= "Mohon untuk segera melakukan pendaftaran Medical Check Up (MCU) terbaru dengan menghubungi OHS Department.\n\n";
            $text .= "Terima kasih atas perhatian dan kerja samanya dalam menjaga kesehatan dan keselamatan kerja.\n\n";
            $text .= "OHS Department";

            return [
                'phone' => $phone,
                'message' => $text
            ];
        }

        $text  = "*PEMBERITAHUAN JADWAL MCU TAHUNAN*\n\n";
        $text .= "Halo Bapak/Ibu {$employeeName},\n";
        $text .= "Anda telah dijadwalkan untuk Medical Check-Up (MCU) pada tanggal *{$date}*.\n";
        $text .= "Mohon persiapkan diri Anda.\n\n";
        $text .= "Terima kasih atas perhatiannya.\n";
        $text .= "OHS Department";

        return [
            'phone' => $phone,
            'message' => $text
        ];
    }
}
