<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PortalInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $clientName,
        public string $email,
        public string $setPasswordUrl
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your client portal access - '.$this->clientName);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.portal-invite', with: [
            'clientName' => $this->clientName,
            'email' => $this->email,
            'setPasswordUrl' => $this->setPasswordUrl,
        ]);
    }
}
