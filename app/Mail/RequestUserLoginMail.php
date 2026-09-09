<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RequestUserLoginMail extends Mailable
{
    use Queueable, SerializesModels;
   public $id_user;
    public $name; // Tambahkan property untuk nama
    /**
     * Create a new message instance.
     */
   public function __construct(public string $email, string $name_req,string $id_users)
    {
        $this->name = $name_req;

        $user = User::where('employee_id', $id_users)->first();
        if ($user) {
            $this->id_user = $user->id;
        }
    }

    /**
     * Get the message envelope.
     */
  public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Request User Login: ' . $this->name, // Subjek lebih dinamis
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.request-login',
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
