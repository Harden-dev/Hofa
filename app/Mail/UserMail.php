<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;
    public $isNewUser;

    public function __construct(User $user, $password, $isNewUser = true)
    {
        $this->user = $user;
        $this->password = $password; // Mot de passe en clair
        $this->isNewUser = $isNewUser;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenue sur la plateforme de HOFA',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user_mail',
            with: [
                'user' => $this->user,
                'password' => $this->password,
                'isNewUser' => $this->isNewUser
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
