<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklySummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $stats
     */
    public function __construct(
        public User $user,
        public array $stats
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Weekly summary - '.now()->startOfWeek()->format('d M').' to '.now()->endOfWeek()->format('d M'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.weekly-summary');
    }
}
