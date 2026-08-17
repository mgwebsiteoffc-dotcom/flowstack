@php
    $links = [
        ['route' => 'settings.index', 'label' => 'Agency settings', 'icon' => '🏢', 'key' => 'general'],
        ['route' => 'settings.users', 'label' => 'Users', 'icon' => '👥', 'key' => 'users'],
        ['route' => 'settings.integrations.lead365', 'label' => 'Lead365', 'icon' => '🔗', 'key' => 'lead365'],
        ['route' => 'settings.integrations.bikribook', 'label' => 'BikriBook', 'icon' => '🧾', 'key' => 'bikribook'],
        ['route' => 'settings.notifications', 'label' => 'Notifications', 'icon' => '🔔', 'key' => 'notifications'],
        ['route' => 'announcements.index', 'label' => 'Announcements', 'icon' => '📣', 'key' => 'announcements'],
        ['route' => 'settings.audit', 'label' => 'Audit log', 'icon' => '📜', 'key' => 'audit'],
        ['route' => 'settings.subscription', 'label' => 'Subscription', 'icon' => '💳', 'key' => 'subscription'],
        ['route' => 'profile.edit', 'label' => 'My profile', 'icon' => '👤', 'key' => 'profile'],
    ];
@endphp
<div class="bg-white rounded-xl border border-gray-100 p-3 space-y-1">
    @foreach ($links as $link)
        <a href="{{ route($link['route']) }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ ($active ?? '') === $link['key'] ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
            <span>{{ $link['icon'] }}</span>{{ $link['label'] }}
        </a>
    @endforeach
</div>
