@extends('layouts.portal')
@section('title', 'Files')
@section('content')
<h1 class="text-xl font-bold text-gray-900 mb-5">Shared files</h1>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse ($files as $file)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="mb-2">
                @if ($file->isImage())<x-icon name="photo" class="w-8 h-8 text-gray-400" />
                @elseif ($file->isPdf())<x-icon name="document" class="w-8 h-8 text-gray-400" />
                @else<x-icon name="paper-clip" class="w-8 h-8 text-gray-400" />@endif
            </div>
            <div class="text-sm font-medium text-gray-800 truncate">{{ $file->original_name }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $file->sizeHuman() }} · {{ $file->created_at->format('d M Y') }}</div>
            <a href="{{ route('portal.files.download', $file) }}" class="text-xs text-indigo-600 mt-2 inline-block"><x-icon name="arrow-down-tray" class="w-4 h-4 inline-block" /> Download</a>
        </div>
    @empty
        <div class="sm:col-span-3 text-center py-12 text-sm text-gray-400">No files shared with you yet.</div>
    @endforelse
</div>
<x-pagination :paginator="$files" />
@endsection
