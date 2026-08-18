@php
    $menuMap = \App\Http\Controllers\AdminUserController::resolveMap();
    $roleMenus = $menuMap[$user->role] ?? \App\Support\MenuPermissions::MENUS;
@endphp
<div class="flex items-center gap-2 px-4 h-14 border-b border-gray-800">
    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-black">A</div>
    <div class="min-w-0">
        <div class="text-white font-bold leading-tight truncate">{{ app('currentTenant')?->name }}</div>
        <div class="text-[10px] text-gray-500 truncate">{{ app('currentTenant')?->slug }}.{{ config('tenancy.tenant_domain') }}</div>
    </div>
    <button @click="mobileNavOpen = false" class="ml-auto lg:hidden text-gray-400 hover:text-white p-1" aria-label="Close menu">
        <x-icon name="x-mark" class="w-5 h-5" />
    </button>
</div>

<nav class="flex-1 overflow-y-auto py-2 px-2.5 space-y-0.5 text-[13px]" @click="mobileNavOpen = false">
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
    <form method="POST" action="{{ route('logout') }}">@csrf
        <button title="Logout" class="text-gray-500 hover:text-white"><x-icon name="logout" class="w-5 h-5" /></button>
    </form>
</div>
