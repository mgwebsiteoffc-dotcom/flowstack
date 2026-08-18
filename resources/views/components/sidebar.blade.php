@auth
@php
    $user = auth()->user();
    $navGroups = [
        'Workspace' => [
            ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'chart-bar', 'key' => 'dashboard'],
        ],
        'Clients' => [
            ['route' => 'clients.index', 'label' => 'Clients', 'icon' => 'users', 'key' => 'clients', 'active' => request()->routeIs('clients*')],
            ['route' => 'projects.index', 'label' => 'Projects', 'icon' => 'folder', 'key' => 'projects', 'active' => request()->routeIs('projects*')],
            ['route' => 'tasks.index', 'label' => 'Tasks', 'icon' => 'check-circle', 'key' => 'tasks', 'active' => request()->routeIs('tasks*')],
        ],
        'Sales' => [
            ['route' => 'leads.index', 'label' => 'Leads', 'icon' => 'target', 'key' => 'leads', 'active' => request()->routeIs('leads*')],
            ['route' => 'proposals.index', 'label' => 'Proposals', 'icon' => 'document-text', 'key' => 'proposals', 'active' => request()->routeIs('proposals*')],
            ['route' => 'reports.index', 'label' => 'Reports', 'icon' => 'chart-bar', 'key' => 'reports', 'active' => request()->routeIs('reports*')],
        ],
        'Finance' => [
            ['route' => 'finance.index', 'label' => 'Finance', 'icon' => 'banknotes', 'key' => 'finance', 'active' => request()->routeIs('finance*')],
        ],
        'Resources' => [
            ['route' => 'kb.index', 'label' => 'Knowledge Base', 'icon' => 'book-open', 'key' => 'kb', 'active' => request()->routeIs('kb*')],
            ['route' => 'files.index', 'label' => 'Files', 'icon' => 'paper-clip', 'key' => 'files', 'active' => request()->routeIs('files*')],
            ['route' => 'time.index', 'label' => 'Time', 'icon' => 'clock', 'key' => 'time', 'active' => request()->routeIs('time*')],
        ],
        'Administration' => [
            ['route' => 'automation.index', 'label' => 'Automation', 'icon' => 'bolt', 'key' => 'automation', 'active' => request()->routeIs('automation*')],
            ['route' => 'team.index', 'label' => 'Team', 'icon' => 'users', 'key' => 'team', 'active' => request()->routeIs('team*')],
            ['route' => 'settings.index', 'label' => 'Settings', 'icon' => 'cog-6-tooth', 'key' => 'settings', 'active' => request()->routeIs('settings*')],
        ],
    ];
@endphp
<aside class="fixed inset-y-0 left-0 w-64 bg-gray-900 text-gray-300 flex flex-col z-40 transition-transform duration-200 -translate-x-full"
       :class="sidebarOpen ? 'translate-x-0' : (isDesktop ? '-translate-x-64' : '-translate-x-full')">
    <div class="flex items-center gap-2 px-4 h-14 border-b border-gray-800">
        <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-black">A</div>
        <div>
            <div class="text-white font-bold leading-tight">{{ app('currentTenant')?->name }}</div>
            <div class="text-[10px] text-gray-500">{{ app('currentTenant')?->slug }}.{{ config('tenancy.tenant_domain') }}</div>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto py-2 px-2.5 space-y-0.5 text-[13px]">
        @php
            $menuMap = \App\Http\Controllers\AdminUserController::resolveMap();
            $roleMenus = $menuMap[$user->role] ?? \App\Support\MenuPermissions::MENUS;
        @endphp
        @foreach ($navGroups as $groupName => $items)
            @php $visible = collect($items)->filter(fn ($i) => ! isset($i['key']) || in_array($i['key'], $roleMenus, true))->filter(fn ($i) => ! ($i['route'] === 'finance.index' && ! $user->canAccessFinance())); @endphp
            @if ($visible->isEmpty()) @continue @endif
            <div class="px-2.5 pt-2.5 pb-0.5 text-[9px] font-semibold uppercase tracking-wider text-gray-500">{{ $groupName }}</div>
            @foreach ($visible as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg transition {{ (isset($item['active']) && $item['active']) || request()->routeIs($item['route'].'*') ? 'bg-gray-800 text-white' : 'hover:bg-gray-800 hover:text-white' }}">
                    <x-icon :name="$item['icon']" class="w-4 h-4 shrink-0" />
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        @endforeach
    </nav>

    <div class="px-3 py-2.5 border-t border-gray-800 flex items-center gap-2.5">
        <x-user-avatar :user="$user" size="md" />
        <div class="min-w-0 flex-1">
            <div class="text-white text-sm truncate">{{ $user->name }}</div>
            <div class="text-xs text-gray-500 capitalize">{{ str_replace('_', ' ', $user->role) }}</div>
        </div>
        <x-pwa-install icon-only label="Install app" />
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button title="Logout" class="text-gray-500 hover:text-white"><x-icon name="logout" class="w-5 h-5" /></button>
        </form>
    </div>
</aside>
@endauth
