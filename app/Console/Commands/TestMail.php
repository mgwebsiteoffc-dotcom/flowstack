<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Quick SMTP smoke test from the CLI (useful on Laragon before opening the app):
 *
 * php artisan app:test-mail
 * php artisan app:test-mail you@example.com
 */
class TestMail extends Command
{
    protected $signature = 'app:test-mail {email? : Recipient (defaults to MAIL_FROM_ADDRESS)}';

    protected $description = 'Send a test email through the configured mailer (Gmail SMTP)';

    public function handle(): int
    {
        $to = $this->argument('email') ?: config('mail.from.address');

        if (! $to) {
            $this->error('No recipient. Pass one: php artisan app:test-mail you@example.com');

            return self::FAILURE;
        }

        $smtp = config('mail.mailers.smtp');
        $this->line('Mailer : '.config('mail.default').' ('.($smtp['host'] ?? '?').':'.($smtp['port'] ?? '?').')');
        $this->line('From   : '.config('mail.from.address').' <'.config('mail.from.name').'>');
        $this->line('To     : '.$to);
        $this->line('');

        try {
            Mail::raw(
                'Test email from Task365 - sent '.now()->format('d M Y H:i').'.'.PHP_EOL.PHP_EOL.
                'If you can read this, your Gmail SMTP settings work.',
                function ($message) use ($to) {
                    $message->to($to)->subject('Task365 test email '.now()->format('d M Y H:i'));
                }
            );
            $this->info('Sent. Check the inbox (and the spam folder).');

            return self::SUCCESS;
        } catch (TransportExceptionInterface | \Throwable $e) {
            $this->error('Failed: '.$e->getMessage());
            $this->line('Tip: Gmail requires an App Password (Google Account > Security > App passwords), not the account password.');

            return self::FAILURE;
        }
    }
}
