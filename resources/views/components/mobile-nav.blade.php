@auth
@php
    $user = auth()->user();
    $menuMap = \App\Http\Controllers\AdminUserController::resolveMap();
    $roleMenus = $menuMap[$user->role] ?? \App\Support\MenuPermissions::MENUS;
    $canMenu = fn (string $key) => in_array($key, $roleMenus, true);
    $canFinance = $user->canAccessFinance();
    $unreadCount = $user->unreadNotifications()->count();

    // Primary tabs (app-style bottom bar, max 5). Filtered by the same
    // role-menu map the sidebar uses.
    $tabs = collect([
        ['route' => 'dashboard', 'label' => 'Home', 'icon' => 'home', 'key' => 'dashboard'],
        ['route' => 'clients.index', 'label' => 'Clients', 'icon' => 'users', 'key' => 'clients'],
        ['route' => 'projects.index', 'label' => 'Projects', 'icon' => 'folder', 'key' => 'projects'],
        ['route' => 'tasks.index', 'label' => 'Tasks', 'icon' => 'check-circle', 'key' => 'tasks'],
    ])->filter(fn ($tab) => $canMenu($tab['key']))->values();

    // Secondary items shown in the "More" bottom sheet.
    $sheetItems = [
        ['route' => 'leads.index', 'label' => 'Leads', 'icon' => 'target', 'key' => 'leads'],
        ['route' => 'proposals.index', 'label' => 'Proposals', 'icon' => 'document-text', 'key' => 'proposals'],
        ['route' => 'reports.index', 'label' => 'Reports', 'icon' => 'chart-bar', 'key' => 'reports'],
        ['route' => 'finance.index', 'label' => 'Finance', 'icon' => 'banknotes', 'key' => 'finance'],
        ['route' => 'kb.index', 'label' => 'Knowledge Base', 'icon' => 'book-open', 'key' => 'kb'],
        ['route' => 'files.index', 'label' => 'Files', 'icon' => 'paper-clip', 'key' => 'files'],
        ['route' => 'time.index', 'label' => 'Time', 'icon' => 'clock', 'key' => 'time'],
        ['route' => 'automation.index', 'label' => 'Automation', 'icon' => 'bolt', 'key' => 'automation'],
        ['route' => 'team.index', 'label' => 'Team', 'icon' => 'users', 'key' => 'team'],
        ['route' => 'settings.index', 'label' => 'Settings', 'icon' => 'cog-6-tooth', 'key' => 'settings'],
        ['route' => 'notifications.index', 'label' => 'Notifications', 'icon' => 'bell', 'key' => 'notifications', 'badge' => $unreadCount],
        ['route' => 'profile.edit', 'label' => 'Profile', 'icon' => 'user', 'key' => 'profile'],
    ];

    $visibleSheet = collect($sheetItems)
        ->filter(fn ($i) => in_array($i['key'], ['notifications', 'profile'], true) || $canMenu($i['key']))
        ->filter(fn ($i) => ! ($i['key'] === 'finance' && ! $canFinance))
        ->values();

    $sheetActive = $visibleSheet->contains(fn ($i) => request()->routeIs($i['route'].'*'));
@endphp

{{-- App-style bottom tab bar (mobile only) --}}
<nav class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur border-t border-gray-200"
     style="padding-bottom: env(safe-area-inset-bottom);">
    <div class="grid grid-cols-5 h-16">
        @foreach ($tabs as $tab)
            @php $active = request()->routeIs($tab['route'].'*'); @endphp
            <a href="{{ route($tab['route']) }}"
               class="flex flex-col items-center justify-center gap-0.5 text-[10px] font-medium {{ $active ? 'text-indigo-600' : 'text-gray-500' }}">
                <span class="flex items-center justify-center rounded-full px-4 py-1 transition {{ $active ? 'bg-indigo-100' : '' }}">
                    <x-icon :name="$tab['icon']" class="w-5 h-5" />
                </span>
                {{ $tab['label'] }}
            </a>
        @endforeach
        <button type="button" @click="moreOpen = true"
                class="flex flex-col items-center justify-center gap-0.5 text-[10px] font-medium {{ $sheetActive ? 'text-indigo-600' : 'text-gray-500' }}">
            <span class="flex items-center justify-center rounded-full px-4 py-1 transition {{ $sheetActive ? 'bg-indigo-100' : '' }}">
                <x-icon name="dots-horizontal" class="w-5 h-5" />
            </span>
            More
        </button>
    </div>
</nav>

{{-- "More" bottom sheet --}}
<div x-show="moreOpen" x-cloak class="fixed inset-0 z-50 md:hidden">
    <div class="absolute inset-0 bg-gray-900/50" @click="moreOpen = false" x-transition.opacity></div>
    <div class="absolute bottom-0 inset-x-0 bg-white rounded-t-3xl shadow-2xl"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         style="padding-bottom: env(safe-area-inset-bottom);">
        <div class="pt-3 pb-1 flex justify-center">
            <div class="w-10 h-1.5 rounded-full bg-gray-200"></div>
        </div>
        <div class="px-5 pt-2 pb-1 flex items-center justify-between">
            <div class="font-bold text-gray-900">More</div>
            <button @click="moreOpen = false" class="text-gray-400 hover:text-gray-600 p-1" aria-label="Close menu">
                <x-icon name="x-mark" class="w-5 h-5" />
            </button>
        </div>
        <div class="px-5 pb-2 text-xs text-gray-400">{{ app('currentTenant')?->name ?? 'Task365' }}</div>

        <div class="px-3 pb-5 max-h-[58vh] overflow-y-auto">
            <div class="grid grid-cols-4 gap-1">
                @foreach ($visibleSheet as $item)
                    <a href="{{ route($item['route']) }}" @click="moreOpen = false"
                       class="flex flex-col items-center gap-1.5 py-3 rounded-2xl active:bg-gray-100 transition">
                        <span class="relative w-11 h-11 rounded-xl bg-gray-100 text-gray-700 flex items-center justify-center">
                            <x-icon :name="$item['icon']" class="w-5 h-5" />
                            @if (! empty($item['badge']) && $item['badge'] > 0)
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[9px] font-bold rounded-full min-w-[16px] h-4 px-0.5 flex items-center justify-center">{{ $item['badge'] > 9 ? '9+' : $item['badge'] }}</span>
                            @endif
                        </span>
                        <span class="text-[10px] text-gray-600 text-center leading-tight">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>

            <div class="mt-4 border-t border-gray-100 pt-3 px-2 space-y-2">
                <x-pwa-install label="Install the app" block />
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold px-4 py-2.5 transition">
                        <x-icon name="logout" class="w-4 h-4" />
                        Log out
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endauth
