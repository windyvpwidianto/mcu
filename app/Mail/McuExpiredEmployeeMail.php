<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class McuExpiredEmployeeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $employeeName;
    public $expiredDate;

    /**
     * Create a new message instance.
     */
    public function __construct(string $employeeName, string $expiredDate)
    {
        $this->employeeName = $employeeName;
        $this->expiredDate = $expiredDate;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pemberitahuan Masa Berlaku Medical Check Up (MCU) Berakhir - ' . $this->employeeName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.mcu_expired_employee',
            with: [
                'employeeName' => $this->employeeName,
                'expiredDate' => $this->expiredDate,
            ]
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
