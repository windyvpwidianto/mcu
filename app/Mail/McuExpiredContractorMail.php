<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class McuExpiredContractorMail extends Mailable
{
    use Queueable, SerializesModels;

    public $contractorName;
    public $expiredEmployees;

    public function __construct(string $contractorName, array $expiredEmployees)
    {
        $this->contractorName = $contractorName;
        $this->expiredEmployees = $expiredEmployees;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Notifikasi MCU Karyawan Expired - ' . $this->contractorName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.mcu_expired_contractor',
            with: [
                'contractorName' => $this->contractorName,
                'expiredEmployees' => $this->expiredEmployees
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
