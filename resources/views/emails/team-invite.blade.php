<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>You're invited to {{ $agencyName }}</title></head>
<body style="font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 24px;">
    <div style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden;">
        <div style="background: #4f46e5; padding: 20px 28px;">
            <span style="color: #ffffff; font-size: 20px; font-weight: 800;">Task<span style="opacity: 0.8;">365</span></span>
        </div>
        <div style="padding: 28px;">
            <h1 style="font-size: 18px; color: #111827; margin: 0 0 12px;">You've been invited to {{ $agencyName }}</h1>
            <p style="font-size: 14px; color: #4b5563; line-height: 1.6;">
                Your role will be <strong>{{ str_replace('_', ' ', $role) }}</strong>. Set your password to join the workspace.
            </p>
            <p style="margin-top: 20px;">
                <a href="{{ $setPasswordUrl }}" style="background: #4f46e5; color: #ffffff; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-size: 14px;">Accept invitation</a>
            </p>
        </div>
    </div>
</body>
</html>
