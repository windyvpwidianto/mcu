<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WpiSubmittedNotification extends Notification
{
    use Queueable;
protected $report;
    /**
     * Create a new notification instance.
     */
    public function __construct($report)
    {
        $this->report = $report;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
       // Mengambil data dari relasi report (asumsi nama relasi)
        $reporterName = $this->report->creator->name ?? 'System';
        $area = $this->report->area ?? 'General Area';
        $date = \Carbon\Carbon::parse($this->report->inspection_date)->format('d F Y');

        // URL menuju halaman detail laporan
        $url = url('/inspeksi/wpi/edit/' . $this->report->id);

        return (new MailMessage)
            ->subject('[WPI] Laporan Inspeksi Baru Menunggu Review')
            ->greeting('Halo ' . $notifiable->name . ',') // $notifiable adalah user Moderator
            ->line('Sebuah laporan Workplace Inspection (WPI) baru telah disubmit dan menunggu review Anda.')
            ->line('**Detail Laporan:**')
            ->line("- **Pelapor:** {$reporterName}")
            ->line("- **Tanggal:** {$date}")
            ->line("- **Area:** {$area}")
            ->action('Lihat Laporan', $url)
            ->line('Terima kasih telah menggunakan aplikasi kami.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
           'report_id' => $this->report->id,
        'title' => 'Laporan WPI Baru',
        'message' => 'Laporan dari ' . ($this->report->creator->name ?? 'System') . ' di area ' . ($this->report->area ?? 'N/A') . ' menunggu review.',
        'url' => url('/inspeksi/wpi/edit/' . $this->report->id),
        'type' => 'WPI_SUBMITTED'
        ];
    }
}
