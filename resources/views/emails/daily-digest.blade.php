<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Your daily digest</title></head>
<body style="font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 24px;">
    <div style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden;">
        <div style="background: #4f46e5; padding: 20px 28px;">
            <span style="color: #ffffff; font-size: 20px; font-weight: 800;">Task<span style="opacity: 0.8;">365</span></span>
        </div>
        <div style="padding: 28px;">
            <h1 style="font-size: 18px; color: #111827; margin: 0 0 4px;">Good morning, {{ $user->name }} hand-raised</h1>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 20px;">Here's your {{ now()->format('l, d M') }} digest.</p>

            <h3 style="font-size: 13px; color: #111827; margin: 16px 0 8px;">calendar Due today</h3>
            @forelse ($dueToday as $title)
                <div style="font-size: 13px; color: #4b5563; padding: 4px 0; border-bottom: 1px solid #f3f4f6;">• {{ $title }}</div>
            @empty
                <div style="font-size: 13px; color: #9ca3af;">Nothing due today</div>
            @endforelse

            <h3 style="font-size: 13px; color: #111827; margin: 16px 0 8px;">Overdue</h3>
            @forelse ($overdue as $title)
                <div style="font-size: 13px; color: #dc2626; padding: 4px 0; border-bottom: 1px solid #f3f4f6;">• {{ $title }}</div>
            @empty
                <div style="font-size: 13px; color: #9ca3af;">Nothing overdue</div>
            @endforelse

            <h3 style="font-size: 13px; color: #111827; margin: 16px 0 8px;">Pending approvals</h3>
            <div style="font-size: 13px; color: #4b5563;">{{ $pendingApprovals }} deliverable(s) waiting for client approval</div>

            <h3 style="font-size: 13px; color: #111827; margin: 16px 0 8px;">megaphone Announcements</h3>
            @forelse ($announcements as $title)
                <div style="font-size: 13px; color: #4b5563; padding: 4px 0; border-bottom: 1px solid #f3f4f6;">• {{ $title }}</div>
            @empty
                <div style="font-size: 13px; color: #9ca3af;">No new announcements</div>
            @endforelse

            <p style="margin-top: 24px;">
                <a href="{{ route('dashboard') }}" style="background: #4f46e5; color: #ffffff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 14px;">Open dashboard</a>
            </p>
        </div>
    </div>
</body>
</html>
