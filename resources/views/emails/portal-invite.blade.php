<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>{{ $clientName }} portal access</title></head>
<body style="font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 24px;">
    <div style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden;">
        <div style="background: #4f46e5; padding: 20px 28px;">
            <span style="color: #ffffff; font-size: 20px; font-weight: 800;">{{ $clientName }} <span style="opacity: 0.8;">Portal</span></span>
        </div>
        <div style="padding: 28px;">
            <h1 style="font-size: 18px; color: #111827; margin: 0 0 12px;">Your client portal is ready 🎉</h1>
            <p style="font-size: 14px; color: #4b5563; line-height: 1.6;">
                You can now track your projects, view reports, approve deliverables, download invoices and submit requests.
            </p>
            <p style="font-size: 13px; color: #6b7280;">Your login email: <strong>{{ $email }}</strong></p>
            <p style="margin-top: 20px;">
                <a href="{{ $setPasswordUrl }}" style="background: #4f46e5; color: #ffffff; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-size: 14px;">Set your password</a>
            </p>
        </div>
    </div>
</body>
</html>
