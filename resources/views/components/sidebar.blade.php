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

{{--
    Desktop sidebar: persistent panel, toggled via the topbar hamburger
    (state persisted in localStorage by appShell()).
--}}
<aside x-show="sidebarOpen" x-cloak
       class="hidden lg:flex fixed inset-y-0 left-0 w-64 bg-gray-900 text-gray-300 flex-col z-30">
    @include('components.sidebar-nav')
</aside>

{{--
    Mobile drawer: slides over the page below the lg breakpoint.
    Backdrop + Escape handling live in layouts/app.blade.php.
--}}
<aside x-show="mobileNavOpen" x-cloak
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="-translate-x-full opacity-50"
       x-transition:enter-end="translate-x-0 opacity-100"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="translate-x-0 opacity-100"
       x-transition:leave-end="-translate-x-full opacity-50"
       @keydown.escape.window="mobileNavOpen = false"
       class="fixed inset-y-0 left-0 w-72 max-w-[85vw] bg-gray-900 text-gray-300 flex flex-col z-50 lg:hidden">
    @include('components.sidebar-nav')
</aside>
@endauth
