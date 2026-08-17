<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $agencyName,
        public string $email,
        public string $role,
        public string $setPasswordUrl
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'You were invited to '.$this->agencyName);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.team-invite');
    }
}
