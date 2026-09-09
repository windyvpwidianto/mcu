<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IncidentAlertMail extends Mailable
{
    use Queueable, SerializesModels;
    public $data; // Public agar otomatis terbaca di blade
    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            // Subjek email yang lebih profesional sesuai standar mining
            subject: 'Significant Incident Alert - ' . $this->data['safety_no'],
        );
    }

    public function content(): Content
    {
        return new Content(
            // Gunakan view() karena template Anda adalah HTML tabel
            view: 'emails.incident-alert',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
