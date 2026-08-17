<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Generic agency notification email.
 *
 * NOTE: the constructor deliberately does NOT use property promotion for
 * `$subject` — Illuminate\Mail\Mailable already declares an untyped public
 * `$subject` property, and PHP fatals if a child class adds a type to an
 * inherited property ("Type of ...::$subject must not be defined").
 * All other payload is stored in private properties to keep the class
 * free of any parent-property collisions.
 */
class AgencyMail extends Mailable
{
    use Queueable, SerializesModels;

    private string $messageText;

    /** @var array<string, mixed> */
    private array $payload;

    private string $templateView;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(string $subject, string $message, array $data = [], string $template = 'emails.notification')
    {
        // Inherited (untyped) Mailable::$subject - assignment is safe here.
        $this->subject = $subject;
        $this->messageText = $message;
        $this->payload = $data;
        $this->templateView = $template;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: $this->templateView,
            with: [
                'subject' => $this->subject,
                'message' => $this->messageText,
                'data' => $this->payload,
            ],
        );
    }
}
