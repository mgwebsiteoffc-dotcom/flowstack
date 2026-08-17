@props(['icon' => '📭', 'title' => 'Nothing here yet', 'message' => '', 'action' => null, 'actionLabel' => ''])
<div class="text-center py-14">
    <div class="text-5xl mb-3">{{ $icon }}</div>
    <h3 class="font-semibold text-gray-900">{{ $title }}</h3>
    @if ($message)<p class="text-sm text-gray-500 mt-1 max-w-md mx-auto">{{ $message }}</p>@endif
    @if ($action)
        <a href="{{ $action }}" class="inline-block mt-4 px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">{{ $actionLabel }}</a>
    @endif
</div>
