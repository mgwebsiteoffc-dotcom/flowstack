<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyDigestMail extends Mailable
{
 use Queueable, SerializesModels;

 /**
 * @param \Illuminate\Support\Collection<int, string> $dueToday
 * @param \Illuminate\Support\Collection<int, string> $overdue
 * @param \Illuminate\Support\Collection<int, string> $announcements
 */
 public function __construct(
 public User $user,
 public $dueToday,
 public $overdue,
 public int $pendingApprovals,
 public $announcements
 ) {
 }

 public function envelope(): Envelope
 {
 return new Envelope(subject: 'Your daily digest - '.now()->format('D, j M Y'));
 }

 public function content(): Content
 {
 return new Content(view: 'emails.daily-digest');
 }
}
