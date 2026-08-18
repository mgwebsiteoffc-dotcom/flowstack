@props(['icon' => 'inbox', 'title' => 'Nothing here yet', 'message' => '', 'action' => null, 'actionLabel' => ''])
<div class="text-center py-14">
    @if ($icon)<x-icon :name="$icon" class="w-12 h-12 mx-auto mb-3 text-gray-300" />@endif
    <h3 class="font-semibold text-gray-900">{{ $title }}</h3>
    @if ($message)<p class="text-sm text-gray-500 mt-1 max-w-md mx-auto">{{ $message }}</p>@endif
    @if ($action)
        <a href="{{ $action }}" class="inline-block mt-4 px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">{{ $actionLabel }}</a>
    @endif
</div>
