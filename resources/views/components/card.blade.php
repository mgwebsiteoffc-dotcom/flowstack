@props(['title' => null, 'icon' => null, 'padding' => true])
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    @if ($title)
        <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                @if ($icon)<x-icon :name="$icon" class="w-4 h-4" />@endif {{ $title }}
            </h3>
            @isset($header){{ $header }}@endisset
        </div>
    @endif
    <div @class(['p-5' => $padding])>
        {{ $slot }}
    </div>
</div>
