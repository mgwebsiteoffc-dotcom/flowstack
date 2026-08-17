@php
    $links = [
        ['route' => 'settings.index', 'label' => 'Agency settings', 'icon' => 'building-office', 'key' => 'general'],
        ['route' => 'settings.users', 'label' => 'Users', 'icon' => 'users', 'key' => 'users'],
        ['route' => 'settings.master.index', 'label' => 'Master data', 'icon' => 'archive-box', 'key' => 'master'],
        ['route' => 'settings.integrations.lead365', 'label' => 'Lead365', 'icon' => 'link', 'key' => 'lead365'],
        ['route' => 'settings.integrations.bikribook', 'label' => 'BikriBook', 'icon' => 'receipt', 'key' => 'bikribook'],
        ['route' => 'settings.notifications', 'label' => 'Notifications', 'icon' => 'bell', 'key' => 'notifications'],
        ['route' => 'announcements.index', 'label' => 'Announcements', 'icon' => 'megaphone', 'key' => 'announcements'],
        ['route' => 'settings.audit', 'label' => 'Audit log', 'icon' => 'document-text', 'key' => 'audit'],
        ['route' => 'settings.subscription', 'label' => 'Subscription', 'icon' => 'credit-card', 'key' => 'subscription'],
        ['route' => 'profile.edit', 'label' => 'My profile', 'icon' => 'user', 'key' => 'profile'],
    ];
@endphp
<div class="bg-white rounded-xl border border-gray-100 p-3 space-y-1">
    @foreach ($links as $link)
        <a href="{{ route($link['route']) }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ ($active ?? '') === $link['key'] ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
            <x-icon :name="$link['icon']" class="w-4 h-4 shrink-0" />{{ $link['label'] }}
        </a>
    @endforeach
</div>
