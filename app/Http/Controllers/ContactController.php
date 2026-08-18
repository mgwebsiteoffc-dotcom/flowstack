<?php

namespace App\Http\Controllers;

use App\Mail\AgencyMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Public contact form - stores messages in platform settings and emails the
 * super admin (from SUPER_ADMIN_EMAIL env).
 */
class ContactController extends Controller
{
    public function index()
    {
        return view('contact');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        // Persist in platform_settings (recent messages list).
        $key = 'contact_messages';
        $messages = json_decode(\App\Models\PlatformSetting::get($key, '[]'), true) ?: [];
        array_unshift($messages, $validated + ['received_at' => now()->toDateTimeString()]);
        $messages = array_slice($messages, 0, 50);
        \App\Models\PlatformSetting::set($key, json_encode($messages));

        // Email the super admin (never blocks the request).
        try {
            Mail::to(config('app.super_admin_email', env('SUPER_ADMIN_EMAIL', 'superadmin@agencyos.test')))
                ->queue(new AgencyMail(
                    'New website enquiry: '.$validated['name'],
                    "Name: {$validated['name']}\nEmail: {$validated['email']}\nCompany: ".($validated['company'] ?? '—')."\n\nMessage:\n".$validated['message']
                ));
        } catch (\Throwable $e) {
            logger()->warning('Contact email could not be sent', ['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Thank you! Your message has been sent. We will get back to you within one business day.');
    }
}
