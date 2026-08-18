@props(['tabs' => []])
<div class="flex gap-1 border-b border-gray-200 mb-5 overflow-x-auto">
    @foreach ($tabs as $tab)
        <a href="{{ $tab['url'] }}"
           class="px-4 py-2.5 text-sm font-medium whitespace-nowrap border-b-2 -mb-px {{ request()->input('tab', 'overview') === $tab['id'] ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-800' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>
