<?php

namespace App\Notifications;

use App\Models\Hazard;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
// use Illuminate\Contracts\Queue\ShouldQueue; // pakai jika ingin dijadikan queue

class HazardSubmittedNotification extends Notification // implements ShouldQueue
{
    use Queueable;

    public function __construct(public Hazard $hazard) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Hazard Report Submitted')
            ->line('A new hazard has been reported.')
            ->action('View Hazard', url('/eventReport/hazardReportDetail/' . $this->hazard->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message'   => 'New hazard report submitted (ID: ' . $this->hazard->id . ')',
            'hazard_id' => $this->hazard->id,
        ];
    }
}
