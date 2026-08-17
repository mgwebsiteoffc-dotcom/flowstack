@extends('layouts.app')
@section('title', 'Files')
@section('breadcrumb', 'Files')
@section('content')
<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <div class="text-sm text-gray-500">
        @foreach ($breadcrumbs as $i => $crumb)
            <a href="{{ route('files.index', ['folder_id' => $crumb->id]) }}" class="text-indigo-600 hover:underline">{{ $crumb->name }}</a>
            @if (! $loop->last) <span>/</span> @endif
        @endforeach
        @if (! $breadcrumbs->isEmpty()) <a href="{{ route('files.index') }}" class="text-gray-400 hover:underline">↩</a> @endif
    </div>
    <div class="flex gap-2">
        @php $usedGb = round($storageUsed / 1024 / 1024 / 1024, 2); @endphp
        <span class="text-xs text-gray-400 self-center">Storage: {{ $usedGb }} / {{ $storageLimit > 0 ? $storageLimit / 1024 / 1024 / 1024 : '∞' }} GB</span>
        <button x-data @click="$refs.uploadModal.showModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium"><x-icon name="arrow-up-tray" class="w-4 h-4 inline-block" /> Upload</button>
        <button x-data @click="$refs.folderModal.showModal()" class="bg-white border border-gray-200 text-gray-600 px-4 py-2 rounded-lg text-sm">+ Folder</button>
    </div>
</div>

<dialog id="upload-modal" x-ref="uploadModal" class="rounded-2xl shadow-2xl p-0 w-full max-w-lg">
    <form method="POST" action="{{ route('files.upload') }}" enctype="multipart/form-data" class="p-6 space-y-4">
        @csrf
        <h3 class="font-semibold text-gray-900">Upload files</h3>
        @if ($currentFolder)
            <input type="hidden" name="folder_id" value="{{ $currentFolder->id }}">
        @endif
        <x-file-upload name="files" :multiple="true" />
        <div class="flex gap-3 justify-end">
            <button type="button" @click="$refs.uploadModal.close()" class="px-4 py-2 text-sm text-gray-500">Cancel</button>
            <button class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg">Upload</button>
        </div>
    </form>
</dialog>

<dialog id="folder-modal" x-ref="folderModal" class="rounded-2xl shadow-2xl p-0 w-full max-w-sm">
    <form method="POST" action="{{ route('files.folders.store') }}" class="p-6 space-y-3">
        @csrf
        <h3 class="font-semibold text-gray-900">New folder</h3>
        <input type="text" name="name" placeholder="Folder name *" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        @if ($currentFolder)
            <input type="hidden" name="parent_folder_id" value="{{ $currentFolder->id }}">
        @endif
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Client (optional)</label>
            <select name="client_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">No client (internal)</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" {{ $currentFolder?->client_id === $client->id ? 'selected' : '' }}>{{ $client->company_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-3 justify-end">
            <button type="button" @click="$refs.folderModal.close()" class="px-4 py-2 text-sm text-gray-500">Cancel</button>
            <button class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg">Create</button>
        </div>
    </form>
</dialog>

<div class="grid lg:grid-cols-4 gap-6">
    <div>
        <x-card title="Folders" icon="folder">
            @if ($folders->isNotEmpty())
                <div class="space-y-1">
                    @foreach ($folders as $folder)
                        <a href="{{ route('files.index', ['folder_id' => $folder->id]) }}"
                           class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-sm {{ $currentFolder?->id === $folder->id ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50' }}">
                            <x-icon name="folder" class="w-4 h-4 inline-block" /> <span class="flex-1">{{ $folder->name }}</span>
                            <span class="text-xs text-gray-400">{{ $folder->files_count }}</span>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-gray-400 text-center py-3">No folders here</p>
            @endif
            @if ($internalFolders->isNotEmpty())
                <div class="border-t mt-3 pt-3">
                    <div class="text-[10px] uppercase text-gray-400 mb-1">Internal</div>
                    @foreach ($internalFolders as $folder)
                        <a href="{{ route('files.index', ['folder_id' => $folder->id]) }}" class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                            <x-icon name="archive-box" class="w-4 h-4 inline-block" /> <span class="flex-1">{{ $folder->name }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </x-card>

        <x-card title="Filter" icon="magnifying-glass">
            <form method="GET" class="space-y-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search files…" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                <select name="type" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                    <option value="">All types</option>
                    @foreach (['pdf', 'jpg', 'png', 'doc', 'xls', 'csv', 'mp4', 'zip'] as $t)
                        <option value="{{ $t }}" {{ $type === $t ? 'selected' : '' }}>{{ strtoupper($t) }}</option>
                    @endforeach
                </select>
                @if ($currentFolder)<input type="hidden" name="folder_id" value="{{ $currentFolder->id }}">@endif
                <button class="w-full bg-gray-800 text-white rounded-lg py-1.5 text-sm">Apply</button>
            </form>
        </x-card>
    </div>

    <div class="lg:col-span-3">
        <x-card :title="'Files ('.$files->total().')'" icon="paper-clip" :padding="false">
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 p-5">
                @forelse ($files as $file)
                    <div class="border border-gray-100 rounded-xl p-4 hover:border-indigo-300 transition" x-data="{ menu: false }">
                        <div class="flex items-start justify-between">
                            <div class="text-3xl">
                                @if ($file->isImage()) <x-icon name="photo" class="w-4 h-4 inline-block" />
                                @elseif ($file->isPdf()) <x-icon name="document" class="w-4 h-4 inline-block" />
                                @elseif ($file->isVideo()) <x-icon name="video-camera" class="w-4 h-4 inline-block" />
                                @else <x-icon name="paper-clip" class="w-4 h-4 inline-block" />
                                @endif
                            </div>
                            <div class="relative">
                                <button @click="menu = !menu" class="text-gray-400 text-lg">⋯</button>
                                <div x-show="menu" x-cloak @click.outside="menu = false" class="absolute right-0 mt-1 w-44 bg-white rounded-xl shadow-xl border text-sm z-20">
                                    @if ($file->isImage() || $file->isPdf())
                                        <button @click="menu = false; $refs.preview{{ $file->id }}.showModal()" class="block w-full text-left px-3 py-2 hover:bg-gray-50"><x-icon name="eye" class="w-4 h-4 inline-block" /> Preview</button>
                                    @endif
                                    <a href="{{ route('files.download', $file) }}" class="block px-3 py-2 hover:bg-gray-50"><x-icon name="arrow-down-tray" class="w-4 h-4 inline-block" /> Download</a>
                                    <button @click="menu = false; $refs.rename{{ $file->id }}.showModal()" class="block w-full text-left px-3 py-2 hover:bg-gray-50"><x-icon name="pencil" class="w-4 h-4 inline-block" /> Rename</button>
                                    @if ($file->share_token)
                                        <a href="{{ $file->shareUrl() }}" target="_blank" class="block px-3 py-2 hover:bg-gray-50"><x-icon name="link" class="w-4 h-4 inline-block" /> Open share link</a>
                                        <form method="POST" action="{{ route('files.unshare', $file) }}">@csrf
                                            <button class="block w-full text-left px-3 py-2 hover:bg-gray-50 text-red-500"><x-icon name="lock-closed" class="w-4 h-4 inline-block" /> Unshare</button>
                                        </form>
                                    @else
                                        <button @click="menu = false; $refs.share{{ $file->id }}.showModal()" class="block w-full text-left px-3 py-2 hover:bg-gray-50"><x-icon name="link" class="w-4 h-4 inline-block" /> Share…</button>
                                    @endif
                                    <form method="POST" action="{{ route('files.version', $file) }}" enctype="multipart/form-data">
                                        @csrf
                                        <label class="block px-3 py-2 hover:bg-gray-50 cursor-pointer"><x-icon name="arrow-up-tray" class="w-4 h-4 inline-block" /> New version
                                            <input type="file" name="file" class="hidden" onchange="this.form.submit()">
                                        </label>
                                    </form>
                                    <form method="POST" action="{{ route('files.destroy', $file) }}" onsubmit="return confirm('Delete this file?')">@csrf @method('DELETE')
                                        <button class="block w-full text-left px-3 py-2 hover:bg-gray-50 text-red-500"><x-icon name="trash" class="w-4 h-4 inline-block" /> Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="text-sm font-medium text-gray-800 truncate mt-2" title="{{ $file->original_name }}">{{ $file->original_name }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ $file->sizeHuman() }} · {{ strtoupper($file->extension ?? '') }} · v{{ $file->version }}</div>
                        <div class="text-xs text-gray-400">{{ $file->uploader?->name }} · {{ $file->created_at->diffForHumans() }}</div>
                        @if ($file->is_shared_with_client)
                            <div class="text-[10px] text-green-600 mt-1"><x-icon name="lock-open" class="w-4 h-4 inline-block" /> Shared with client</div>
                        @endif

                        <dialog :id="'rename-{{ $file->id }}'" x-ref="rename{{ $file->id }}" class="rounded-2xl shadow-2xl p-0 w-full max-w-sm">
                            <form method="POST" action="{{ route('files.update', $file) }}" class="p-5 space-y-3">
                                @csrf @method('PATCH')
                                <h3 class="font-semibold text-sm">Rename / move file</h3>
                                <input type="text" name="original_name" value="{{ $file->original_name }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <select name="folder_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="">Root (no folder)</option>
                                    @foreach ($folders as $folderOption)
                                        <option value="{{ $folderOption->id }}" {{ $file->folder_id === $folderOption->id ? 'selected' : '' }}>{{ $folderOption->name }}</option>
                                    @endforeach
                                </select>
                                <div class="flex gap-2 justify-end">
                                    <button type="button" @click="$refs['rename{{ $file->id }}'].close()" class="px-3 py-1.5 text-sm text-gray-500">Cancel</button>
                                    <button class="px-3 py-1.5 text-sm bg-indigo-600 text-white rounded-lg">Save</button>
                                </div>
                            </form>
                        </dialog>

                        @if ($file->isImage() || $file->isPdf())
                            <dialog :id="'preview-{{ $file->id }}'" x-ref="preview{{ $file->id }}" class="rounded-2xl shadow-2xl p-4 w-full max-w-3xl">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="font-semibold text-sm text-gray-900 truncate">{{ $file->original_name }}</h3>
                                    <button type="button" @click="$refs['preview{{ $file->id }}'].close()" class="text-gray-400 hover:text-gray-600 text-xl">×</button>
                                </div>
                                @if ($file->isImage())
                                    <img src="{{ route('files.preview', $file) }}" alt="{{ $file->original_name }}" class="w-full rounded-lg max-h-[70vh] object-contain">
                                @else
                                    <iframe src="{{ route('files.preview', $file) }}" class="w-full h-[70vh] rounded-lg border border-gray-200"></iframe>
                                @endif
                            </dialog>
                        @endif

                        <dialog :id="'share-{{ $file->id }}'" x-ref="share{{ $file->id }}" class="rounded-2xl shadow-2xl p-0 w-full max-w-sm">
                            <form method="POST" action="{{ route('files.share', $file) }}" class="p-5 space-y-3">
                                @csrf
                                <h3 class="font-semibold text-sm">Create share link</h3>
                                <input type="number" name="expires_in_days" placeholder="Expires in days (optional)" min="1" max="365" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <label class="flex items-center gap-2 text-sm text-gray-600">
                                    <input type="checkbox" name="is_shared_with_client" value="1" class="rounded"> Also share with client portal
                                </label>
                                <div class="flex gap-2 justify-end">
                                    <button type="button" @click="$refs['share{{ $file->id }}'].close()" class="px-3 py-1.5 text-sm text-gray-500">Cancel</button>
                                    <button class="px-3 py-1.5 text-sm bg-indigo-600 text-white rounded-lg">Create link</button>
                                </div>
                            </form>
                        </dialog>
                    </div>
                @empty
                    <div class="sm:col-span-3">
                        <x-empty-state icon="paper-clip" title="No files here" message="Upload files or create a folder to get started." />
                    </div>
                @endforelse
            </div>
        </x-card>
        <div class="mt-4">{{ $files->links() }}</div>
    </div>
</div>
@endsection
