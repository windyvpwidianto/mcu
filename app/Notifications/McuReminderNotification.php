<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Models\McuRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
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

        $channels = [WhatsAppChannel::class];
        if (!empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $employeeName = $this->participant->employee->name ?? ($notifiable->name ?? 'Karyawan');
        $formattedDate = $this->participant->mcu_date ? Carbon::parse($this->participant->mcu_date)->translatedFormat('d F Y') : '-';

        if (in_array($this->type, ['expired', 'overdue'])) {
            return (new MailMessage)
                ->subject('Pemberitahuan Masa Berlaku Medical Check Up (MCU) Berakhir - ' . $employeeName)
                ->greeting("Yth. Bapak/Ibu {$employeeName},")
                ->line("Kami informasikan bahwa masa berlaku Medical Check Up (MCU) Anda telah berakhir pada tanggal {$formattedDate}.")
                ->line("Mohon untuk segera melakukan pendaftaran Medical Check Up (MCU) terbaru dengan menghubungi OHS Department.")
                ->line("Terima kasih atas perhatian dan kerja samanya dalam menjaga kesehatan dan keselamatan kerja.")
                ->salutation("OHS Department");
        }

        return (new MailMessage)
            ->subject('Pemberitahuan Jadwal MCU Tahunan - ' . $employeeName)
            ->greeting("Halo Bapak/Ibu {$employeeName},")
            ->line("Anda telah dijadwalkan untuk Medical Check-Up (MCU) pada tanggal {$formattedDate}.")
            ->line("Mohon persiapkan diri Anda dan memastikan Anda dapat hadir pada jadwal yang ditentukan.")
            ->salutation("OHS Department");
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
