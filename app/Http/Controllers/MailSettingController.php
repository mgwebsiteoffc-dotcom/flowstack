<?php

namespace App\Http\Controllers;

use App\Support\EnvEditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Global outgoing email configuration (Gmail SMTP). Writes MAIL_* values
 * to .env so every mailable - welcome emails, invoices, reports, portal
 * invites, alerts - uses the same sending account. Admin role only.
 */
class MailSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index()
    {
        $smtp = config('mail.mailers.smtp');

        return view('settings.mail', [
            'mailer' => (string) config('mail.default', 'log'),
            'host' => (string) ($smtp['host'] ?? 'smtp.gmail.com'),
            'port' => (string) ($smtp['port'] ?? 587),
            'username' => (string) ($smtp['username'] ?? ''),
            'encryption' => (string) ($smtp['encryption'] ?? 'tls'),
            'fromAddress' => (string) config('mail.from.address', ''),
            'fromName' => (string) config('mail.from.name', config('app.name')),
        ]);
    }

    public function save(Request $request)
    {
        $data = $this->validate($request, [
            'mailer' => 'required|in:smtp,log',
            'host' => 'nullable|string|max:255',
            'port' => 'nullable|integer|min:1|max:65535',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'encryption' => 'nullable|in:tls,ssl',
            'from_address' => 'nullable|email|max:255',
            'from_name' => 'nullable|string|max:255',
        ]);

        $pairs = [
            'MAIL_MAILER' => $data['mailer'],
            'MAIL_FROM_NAME' => $data['from_name'] !== '' && $data['from_name'] !== null
                ? $data['from_name']
                : (string) config('app.name'),
        ];

        if ($data['mailer'] === 'smtp') {
            $pairs['MAIL_HOST'] = $data['host'] !== '' && $data['host'] !== null ? $data['host'] : 'smtp.gmail.com';
            $pairs['MAIL_PORT'] = (string) (($data['port'] !== '' && $data['port'] !== null) ? $data['port'] : 587);
            $pairs['MAIL_USERNAME'] = (string) ($data['username'] ?? '');
            $pairs['MAIL_ENCRYPTION'] = $data['encryption'] ?: 'tls';
            $pairs['MAIL_FROM_ADDRESS'] = $data['from_address'] ?: ($data['username'] ?? '');
        } else {
            // log mailer - keep placeholders so the doctor stays quiet.
            $pairs['MAIL_HOST'] = '127.0.0.1';
            $pairs['MAIL_PORT'] = '2525';
            $pairs['MAIL_USERNAME'] = 'null';
            $pairs['MAIL_PASSWORD'] = 'null';
            $pairs['MAIL_ENCRYPTION'] = 'null';
        }

        if ($data['password'] !== '' && $data['password'] !== null) {
            $pairs['MAIL_PASSWORD'] = $data['password'];
        }

        EnvEditor::set($pairs);
        Artisan::call('config:clear');

        return back()->with('success', 'Mail settings saved and applied. Send a test email to confirm Gmail works.');
    }

    public function test(Request $request)
    {
        $to = auth()->user()->email ?? $request->input('to', '');

        if (! $to) {
            return back()->withErrors(['test' => 'No recipient - your account has no email address.']);
        }

        try {
            Mail::raw(
                "This is a test email from Task365 (".url('/').").\n\n".
                'If you are reading this, your Gmail SMTP settings are working correctly.',
                function ($message) use ($to) {
                    $message->to($to)->subject('Task365 - test email '.now()->format('d M Y H:i'));
                }
            );

            return back()->with('success', 'Test email sent to '.$to.'. Check the inbox (and the spam folder).');
        } catch (TransportExceptionInterface | \Throwable $e) {
            return back()->withErrors(['test' => 'Could not send: '.$e->getMessage()]);
        }
    }
}
