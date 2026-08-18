<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $subject }}</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 24px;">
    <div style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden;">
        <div style="background: #4f46e5; padding: 20px 28px;">
            <span style="color: #ffffff; font-size: 20px; font-weight: 800;">Task<span style="opacity: 0.8;">365</span></span>
        </div>
        <div style="padding: 28px;">
            <h1 style="font-size: 18px; color: #111827; margin: 0 0 12px;">{{ $subject }}</h1>
            <p style="font-size: 14px; color: #4b5563; line-height: 1.6; white-space: pre-line;">{{ $message }}</p>
            @if (! empty($data['portal_url']))
                <p style="margin-top: 16px;">
                    <a href="{{ $data['portal_url'] }}" style="background: #4f46e5; color: #ffffff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 14px;">Open portal</a>
                </p>
            @endif
        </div>
        <div style="background: #f9fafb; padding: 14px 28px; text-align: center; font-size: 11px; color: #9ca3af;">
            Sent by Task365 · {{ now()->format('d M Y') }}
        </div>
    </div>
</body>
</html>
