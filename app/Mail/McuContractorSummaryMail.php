<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Contractor;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class McuContractorSummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $contractor;
    public $users;
    public $stage;

    /**
     * Create a new message instance.
     */
    public function __construct(Contractor $contractor, Collection $users, $stage)
    {
        $this->contractor = $contractor;
        $this->users = $users;
        $this->stage = $stage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $monthYear = Carbon::now()->translatedFormat('F Y');
        $subject = "Rekap Jadwal MCU Tahunan - {$this->contractor->contractor_name} - {$monthYear}";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.mcu_contractor_summary',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // Here we could generate an Excel/PDF and attach it.
        // For simplicity, we just send the email with the summary table for now.
        return [];
    }
}
