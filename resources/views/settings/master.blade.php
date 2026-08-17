@extends('layouts.app')
@section('title', 'Master data')
@section('breadcrumb', 'Settings / Master data')
@section('content')
<div class="grid lg:grid-cols-4 gap-6">
    <div>@include('settings.partials.nav', ['active' => 'master'])</div>
    <div class="lg:col-span-3 space-y-6">
        <x-card title="Expense categories" icon="banknotes">
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach ($categories as $category)
                    <span class="inline-flex items-center gap-2 bg-gray-100 rounded-full px-3 py-1 text-sm">
                        <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $category->color }}"></span>{{ $category->name }}
                        <form method="POST" action="{{ route('settings.master.category.destroy', $category) }}" class="inline" onsubmit="return confirm('Delete this category?')">@csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-600"><x-icon name="x-mark" class="w-3 h-3" /></button>
                        </form>
                    </span>
                @endforeach
            </div>
            <form method="POST" action="{{ route('settings.master.category.store') }}" class="flex gap-2">
                @csrf
                <input type="text" name="name" placeholder="Category name *" required class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input type="color" name="color" value="#6B7280" class="w-12 h-10 rounded-lg border border-gray-300">
                <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">Add</button>
            </form>
        </x-card>

        <x-card title="Task tags" icon="tag">
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach ($tags as $tag)
                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm" style="background: {{ $tag->color }}22; color: {{ $tag->color }}">
                        {{ $tag->name }}
                        <form method="POST" action="{{ route('settings.master.tag.destroy', $tag) }}" class="inline" onsubmit="return confirm('Delete this tag?')">@csrf @method('DELETE')
                            <button class="opacity-60 hover:opacity-100"><x-icon name="x-mark" class="w-3 h-3" /></button>
                        </form>
                    </span>
                @endforeach
            </div>
            <form method="POST" action="{{ route('settings.master.tag.store') }}" class="flex gap-2">
                @csrf
                <input type="text" name="name" placeholder="Tag name *" required class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input type="color" name="color" value="#6B7280" class="w-12 h-10 rounded-lg border border-gray-300">
                <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">Add</button>
            </form>
        </x-card>

        <x-card title="Service types" icon="bolt">
            <p class="text-xs text-gray-400 mb-3">Built-in services are always available. Add custom service types here - they appear in client, project and task forms.</p>
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach ($defaultServices as $slug => $label)
                    <span class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-700 rounded-full px-3 py-1 text-sm">
                        {{ $label }}
                        <span class="text-[10px] opacity-60">{{ $slug }}</span>
                    </span>
                @endforeach
                @foreach ($services as $service)
                    <span class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 rounded-full px-3 py-1 text-sm">
                        {{ $service->name }}
                        <span class="text-[10px] opacity-60">{{ $service->meta['slug'] ?? '' }}</span>
                        <form method="POST" action="{{ route('settings.master.service.destroy', $service) }}" class="inline" onsubmit="return confirm('Remove this service type?')">@csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-600"><x-icon name="x-mark" class="w-3 h-3" /></button>
                        </form>
                    </span>
                @endforeach
            </div>
            <form method="POST" action="{{ route('settings.master.service.store') }}" class="flex gap-2">
                @csrf
                <input type="text" name="name" placeholder="Service name * (e.g. Email Marketing)" required class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input type="color" name="color" value="#6B7280" class="w-12 h-10 rounded-lg border border-gray-300">
                <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">Add</button>
            </form>
        </x-card>
    </div>
</div>
@endsection
