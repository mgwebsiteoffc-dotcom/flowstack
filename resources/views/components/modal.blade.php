@props(['id' => 'modal', 'title' => '', 'maxWidth' => 'max-w-lg'])
<div x-data="{ open: false }" x-cloak>
    <div @click="open = true" class="inline">{{ $trigger ?? '' }}</div>
    <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="fixed inset-0 bg-black/40" @click="open = false"></div>
        <div class="relative min-h-full flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full {{ $maxWidth }}">
                <div class="flex items-center justify-between px-5 py-4 border-b">
                    <h3 class="font-semibold text-gray-900">{{ $title }}</h3>
                    <button @click="open = false" class="text-gray-400 hover:text-gray-600 text-xl">×</button>
                </div>
                <div class="p-5">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>
