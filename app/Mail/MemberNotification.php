<?php

namespace App\Mail;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $member;
    public $isAdmin;
    public $type;
    public $customMessage;
    public $reason;

    public function __construct(Member $member, $isAdmin = false, $type = 'created', $customMessage = null, $reason = null)
    {
        $this->member = $member;
        $this->isAdmin = $isAdmin;
        $this->type = $type;
        $this->customMessage = $customMessage;
        $this->reason = $reason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match($this->type) {
            'approved' => $this->isAdmin
                ? '✅ Membre approuvé sur ' . config('app.name')
                : '✅ Votre demande d\'adhésion a été approuvée !',
            'rejected' => $this->isAdmin
                ? '❌ Membre rejeté sur ' . config('app.name')
                : '❌ Réponse à votre demande d\'adhésion',
            default => $this->isAdmin
                ? '🎉 Nouveau membre sur ' . config('app.name')
                : '🙏 Merci pour votre inscription !',
        };

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $view = match($this->type) {
            'approved' => 'emails.member_approved',
            'rejected' => 'emails.member_rejected',
            default => 'emails.membre_notification',
        };

        return new Content(
            view: $view,
            with: [
                'member' => $this->member,
                'isAdmin' => $this->isAdmin,
                'type' => $this->type,
                'customMessage' => $this->customMessage,
                'reason' => $this->reason
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
