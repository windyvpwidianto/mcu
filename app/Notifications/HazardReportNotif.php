<?php

namespace App\Notifications;

use App\Models\Hazard;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class HazardReportNotif extends Notification 
{
    use Queueable;
    /**
     * Create a new notification instance.
     */
    public function __construct(public Hazard $hazard){}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Laporan Hazard Baru Dibuat!')
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Ada laporan hazard baru yang perlu Anda tinjau.')
            ->action('Lihat Laporan Hazard', url('/hazards/' . $this->hazard->id))
            ->line('Nomor Referensi: ' . $this->hazard->no_referensi)
            ->line('Lokasi: ' . optional($this->hazard->location)->name)
            ->line('Tipe Bahaya: ' . optional($this->hazard->eventType)->event_type_name)
            ->line('Terima kasih telah menggunakan aplikasi kami!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'hazard_id' => $this->hazard->id,
            'no_referensi' => $this->hazard->no_referensi,
            'message' => 'Laporan hazard baru dengan nomor referensi ' . $this->hazard->no_referensi . ' telah dibuat.',
            'url' => url('/hazards/' . $this->hazard->id),
        ];
    }
}
