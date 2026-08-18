<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Weekly summary</title></head>
<body style="font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 24px;">
    <div style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden;">
        <div style="background: #4f46e5; padding: 20px 28px;">
            <span style="color: #ffffff; font-size: 20px; font-weight: 800;">Task<span style="opacity: 0.8;">365</span></span>
        </div>
        <div style="padding: 28px;">
            <h1 style="font-size: 18px; color: #111827; margin: 0 0 4px;">Weekly summary, {{ $user->name }}</h1>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 20px;">{{ now()->startOfWeek()->format('d M') }} – {{ now()->endOfWeek()->format('d M') }}</p>
            <table style="width: 100%; font-size: 14px;">
                <tr><td style="padding: 8px 0; color: #4b5563;">check-circle Tasks completed</td><td style="text-align: right; font-weight: 700; color: #111827;">{{ $stats['tasks_completed'] }}</td></tr>
                <tr><td style="padding: 8px 0; color: #4b5563;">Hours logged</td><td style="text-align: right; font-weight: 700; color: #111827;">{{ $stats['hours_logged'] }}h</td></tr>
                <tr><td style="padding: 8px 0; color: #4b5563;">target New leads</td><td style="text-align: right; font-weight: 700; color: #111827;">{{ $stats['new_leads'] }}</td></tr>
                <tr><td style="padding: 8px 0; color: #4b5563;">banknotes Revenue collected</td><td style="text-align: right; font-weight: 700; color: #059669;">₹{{ number_format($stats['revenue']) }}</td></tr>
            </table>
            <p style="margin-top: 24px;">
                <a href="{{ route('dashboard') }}" style="background: #4f46e5; color: #ffffff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 14px;">Open dashboard</a>
            </p>
        </div>
    </div>
</body>
</html>
