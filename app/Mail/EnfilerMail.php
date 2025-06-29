<?php

namespace App\Mail;

use App\Models\Enfiler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnfilerMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $enfiler;
    public $isAdmin;
    public function __construct(Enfiler $enfiler, $isAdmin = false)
    {
        //
        $this->enfiler = $enfiler;
        $this->isAdmin = $isAdmin;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->isAdmin ? '🎉 Nouveau don reçu sur ' . config('app.name') : '🙏 Merci pour votre don !',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.don_notification',
            with: [
                'enfiler' => $this->enfiler,
                'isAdmin' => $this->isAdmin
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
