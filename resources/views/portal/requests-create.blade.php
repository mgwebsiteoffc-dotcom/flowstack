@extends('layouts.portal')
@section('title', 'New request')
@section('content')
<h1 class="text-xl font-bold text-gray-900 mb-5">Submit a request</h1>
<form method="POST" action="{{ route('portal.requests.store') }}" enctype="multipart/form-data" class="max-w-2xl space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Request type *</label>
        <select name="request_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            @foreach (['general' => 'General', 'content' => 'Content', 'design' => 'Design', 'technical' => 'Technical', 'report' => 'Report'] as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
        <input type="text" name="title" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
        <textarea name="description" rows="5" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Priority *</label>
        <select name="priority" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="normal">Normal</option>
            <option value="urgent">Urgent</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Attachments (optional, up to 5)</label>
        <input type="file" name="attachments[]" multiple class="text-sm">
    </div>
    <div class="flex gap-3">
        <a href="{{ route('portal.requests') }}" class="px-5 py-2 rounded-lg text-sm text-gray-500">Cancel</a>
        <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Submit request</button>
    </div>
</form>
@endsection
