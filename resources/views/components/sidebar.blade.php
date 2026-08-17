@auth
@php
    $user = auth()->user();
    $nav = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'chart-bar', 'key' => 'dashboard'],
        ['route' => 'clients.index', 'label' => 'Clients', 'icon' => 'users', 'active' => request()->routeIs('clients*'), 'key' => 'clients'],
        ['route' => 'projects.index', 'label' => 'Projects', 'icon' => 'folder', 'active' => request()->routeIs('projects*'), 'key' => 'projects'],
        ['route' => 'tasks.index', 'label' => 'Tasks', 'icon' => 'check-circle', 'active' => request()->routeIs('tasks*'), 'key' => 'tasks'],
        ['route' => 'leads.index', 'label' => 'Leads', 'icon' => 'target', 'active' => request()->routeIs('leads*'), 'key' => 'leads'],
        ['route' => 'finance.index', 'label' => 'Finance', 'icon' => 'banknotes', 'active' => request()->routeIs('finance*'), 'key' => 'finance'],
        ['route' => 'proposals.index', 'label' => 'Proposals', 'icon' => 'document-text', 'active' => request()->routeIs('proposals*'), 'key' => 'proposals'],
        ['route' => 'reports.index', 'label' => 'Reports', 'icon' => 'chart-bar', 'active' => request()->routeIs('reports*'), 'key' => 'reports'],
        ['route' => 'kb.index', 'label' => 'Knowledge Base', 'icon' => 'book-open', 'active' => request()->routeIs('kb*'), 'key' => 'kb'],
        ['route' => 'files.index', 'label' => 'Files', 'icon' => 'paper-clip', 'active' => request()->routeIs('files*'), 'key' => 'files'],
        ['route' => 'time.index', 'label' => 'Time', 'icon' => 'clock', 'active' => request()->routeIs('time*'), 'key' => 'time'],
        ['route' => 'automation.index', 'label' => 'Automation', 'icon' => 'bolt', 'active' => request()->routeIs('automation*'), 'key' => 'automation'],
        ['route' => 'team.index', 'label' => 'Team', 'icon' => 'users', 'active' => request()->routeIs('team*'), 'key' => 'team'],
        ['route' => 'settings.index', 'label' => 'Settings', 'icon' => 'cog-6-tooth', 'active' => request()->routeIs('settings*'), 'key' => 'settings'],
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
            @if (isset($item['key']) && ! \App\Support\MenuPermissions::can($user->role, $item['key'])) @continue @endif
            @if ($item['route'] === 'finance.index' && ! $user->canAccessFinance()) @continue @endif
            @if (in_array($item['route'], ['reports.index', 'team.index', 'clients.index', 'leads.index', 'automation.index']) && $user->isSpecialist()) @continue @endif
            <a href="{{ route($item['route']) }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ (isset($item['active']) && $item['active']) || request()->routeIs($item['route'].'*') ? 'bg-gray-800 text-white' : 'hover:bg-gray-800 hover:text-white' }}">
                <x-icon :name="$item['icon']" class="w-5 h-5 shrink-0" />
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
            <button title="Logout" class="text-gray-500 hover:text-white"><x-icon name="logout" class="w-5 h-5" /></button>
        </form>
    </div>
</aside>
@endauth
