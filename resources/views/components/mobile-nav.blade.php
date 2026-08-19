@auth
@php
    $user = auth()->user();
    $menuMap = \App\Http\Controllers\AdminUserController::resolveMap();
    $roleMenus = $menuMap[$user->role] ?? \App\Support\MenuPermissions::MENUS;
    $can = fn ($key) => in_array($key, $roleMenus, true);

    // Primary tabs (4) shown on the bar; the rest live in the "More" sheet.
    $primary = collect([
        ['route' => 'dashboard',        'label' => 'Home',   'icon' => 'home',        'key' => 'dashboard'],
        ['route' => 'tasks.index',      'label' => 'Tasks',  'icon' => 'check-circle', 'key' => 'tasks'],
        ['route' => 'clients.index',    'label' => 'Clients','icon' => 'users',        'key' => 'clients'],
        ['route' => 'leads.index',      'label' => 'Leads',  'icon' => 'target',       'key' => 'leads'],
    ])->filter(fn ($i) => $can($i['key']));

    $more = collect([
        ['route' => 'tasks.today',      'label' => 'Today',  'icon' => 'sun',           'key' => 'tasks'],
        ['route' => 'projects.index',   'label' => 'Projects','icon' => 'folder',        'key' => 'projects'],
        ['route' => 'proposals.index',  'label' => 'Proposals','icon' => 'document-text', 'key' => 'proposals'],
        ['route' => 'reports.index',    'label' => 'Reports', 'icon' => 'chart-bar',      'key' => 'reports'],
        ['route' => 'finance.index',    'label' => 'Finance', 'icon' => 'banknotes',      'key' => 'finance'],
        ['route' => 'kb.index',         'label' => 'Knowledge Base', 'icon' => 'book-open', 'key' => 'kb'],
        ['route' => 'files.index',      'label' => 'Files',   'icon' => 'paper-clip',     'key' => 'files'],
        ['route' => 'time.index',       'label' => 'Time',    'icon' => 'clock',          'key' => 'time'],
        ['route' => 'automation.index', 'label' => 'Automation','icon' => 'bolt',         'key' => 'automation'],
        ['route' => 'team.index',       'label' => 'Team',    'icon' => 'users',          'key' => 'team'],
        ['route' => 'settings.index',   'label' => 'Settings','icon' => 'cog-6-tooth',     'key' => 'settings'],
    ])->filter(function ($i) use ($can, $user) {
        if ($i['route'] === 'finance.index' && ! $user->canAccessFinance()) {
            return false;
        }

        return $can($i['key']);
    });

    $isActive = fn ($route) => request()->routeIs($route.'*')
        || ($route === 'dashboard' && request()->routeIs('dashboard'));
@endphp

<nav x-data="{ moreOpen: false, installable: false }"
     x-init="window.addEventListener('app:installable', () => installable = true)"
     class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white border-t border-gray-200 pb-[env(safe-area-inset-bottom)]">
    <div class="flex h-16">
        @foreach ($primary as $item)
            <a href="{{ route($item['route']) }}"
               class="flex-1 flex flex-col items-center justify-center gap-0.5 {{ $isActive($item['route']) ? 'text-indigo-600' : 'text-gray-500' }}">
                <x-icon :name="$item['icon']" class="w-6 h-6" />
                <span class="text-[10px] font-medium leading-none">{{ $item['label'] }}</span>
            </a>
        @endforeach
        <button @click="moreOpen = true"
                class="flex-1 flex flex-col items-center justify-center gap-0.5 text-gray-500">
            <x-icon name="ellipsis-horizontal" class="w-6 h-6" />
            <span class="text-[10px] font-medium leading-none">More</span>
        </button>
    </div>

    <!-- More sheet -->
    <div x-cloak x-show="moreOpen" class="fixed inset-0 z-50" @keydown.escape.window="moreOpen = false">
        <div class="absolute inset-0 bg-black/40" x-transition.opacity @click="moreOpen = false"></div>
        <div x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             class="absolute bottom-0 inset-x-0 bg-white rounded-t-2xl max-h-[85vh] overflow-y-auto pb-[env(safe-area-inset-bottom)]">
            <div class="sticky top-0 bg-white px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <span class="font-semibold text-gray-900">Menu</span>
                <button @click="moreOpen = false" class="text-gray-400 hover:text-gray-600 p-1"><x-icon name="x-mark" class="w-5 h-5" /></button>
            </div>

            <div class="grid grid-cols-4 gap-2 p-4">
                @foreach ($more as $item)
                    <a href="{{ route($item['route']) }}"
                       class="flex flex-col items-center gap-1.5 py-2 rounded-xl {{ $isActive($item['route']) ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50' }}">
                        <x-icon :name="$item['icon']" class="w-6 h-6" />
                        <span class="text-[10px] font-medium text-center leading-tight">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>

            <div class="px-4 pt-1 pb-2 border-t border-gray-100">
                <button x-show="installable" @click="window.installApp().then(() => installable = false)"
                        class="w-full flex items-center gap-3 px-3 py-3 rounded-xl bg-indigo-50 text-indigo-700 text-sm font-medium">
                    <x-icon name="arrow-down-tray" class="w-5 h-5" /> Install app
                </button>
                <button x-show="!installable" @click="window.installApp()"
                        class="w-full flex items-center gap-3 px-3 py-3 rounded-xl text-gray-500 text-sm hover:bg-gray-50">
                    <x-icon name="device-phone-mobile" class="w-5 h-5" /> Add to Home Screen
                </button>

                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                    @csrf
                    <button class="w-full flex items-center gap-3 px-3 py-3 rounded-xl text-red-600 text-sm font-medium hover:bg-red-50">
                        <x-icon name="logout" class="w-5 h-5" /> Log out
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
@endauth
