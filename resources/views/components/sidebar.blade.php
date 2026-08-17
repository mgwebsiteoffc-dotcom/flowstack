@auth
@php
    $user = auth()->user();
    $nav = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => '📊'],
        ['route' => 'clients.index', 'label' => 'Clients', 'icon' => '🤝', 'active' => request()->routeIs('clients*')],
        ['route' => 'projects.index', 'label' => 'Projects', 'icon' => '📁', 'active' => request()->routeIs('projects*')],
        ['route' => 'tasks.index', 'label' => 'Tasks', 'icon' => '✅', 'active' => request()->routeIs('tasks*')],
        ['route' => 'leads.index', 'label' => 'Leads', 'icon' => '🎯', 'active' => request()->routeIs('leads*')],
        ['route' => 'finance.index', 'label' => 'Finance', 'icon' => '💰', 'active' => request()->routeIs('finance*')],
        ['route' => 'reports.index', 'label' => 'Reports', 'icon' => '📈', 'active' => request()->routeIs('reports*')],
        ['route' => 'kb.index', 'label' => 'Knowledge Base', 'icon' => '📚', 'active' => request()->routeIs('kb*')],
        ['route' => 'files.index', 'label' => 'Files', 'icon' => '📎', 'active' => request()->routeIs('files*')],
        ['route' => 'time.index', 'label' => 'Time', 'icon' => '⏱️', 'active' => request()->routeIs('time*')],
        ['route' => 'automation.index', 'label' => 'Automation', 'icon' => '⚡', 'active' => request()->routeIs('automation*')],
        ['route' => 'team.index', 'label' => 'Team', 'icon' => '👥', 'active' => request()->routeIs('team*')],
        ['route' => 'settings.index', 'label' => 'Settings', 'icon' => '⚙️', 'active' => request()->routeIs('settings*')],
    ];
@endphp
<aside x-show="sidebarOpen" x-transition
       class="fixed inset-y-0 left-0 w-64 bg-gray-900 text-gray-300 flex flex-col z-40">
    <div class="flex items-center gap-2 px-5 h-16 border-b border-gray-800">
        <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-black">A</div>
        <div>
            <div class="text-white font-bold leading-tight">{{ app('currentTenant')?->name }}</div>
            <div class="text-[10px] text-gray-500">{{ app('currentTenant')?->slug }}.{{ config('tenancy.tenant_domain') }}</div>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5 text-sm">
        @foreach ($nav as $item)
            @if ($item['route'] === 'finance.index' && ! $user->canAccessFinance()) @continue @endif
            @if (in_array($item['route'], ['reports.index', 'team.index', 'clients.index', 'leads.index', 'automation.index']) && $user->isSpecialist()) @continue @endif
            <a href="{{ route($item['route']) }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ (isset($item['active']) && $item['active']) || request()->routeIs($item['route'].'*') ? 'bg-gray-800 text-white' : 'hover:bg-gray-800 hover:text-white' }}">
                <span class="text-base">{{ $item['icon'] }}</span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="p-4 border-t border-gray-800 flex items-center gap-3">
        <x-user-avatar :user="$user" size="md" />
        <div class="min-w-0 flex-1">
            <div class="text-white text-sm truncate">{{ $user->name }}</div>
            <div class="text-xs text-gray-500 capitalize">{{ str_replace('_', ' ', $user->role) }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button title="Logout" class="text-gray-500 hover:text-white text-lg">⏻</button>
        </form>
    </div>
</aside>
@endauth
