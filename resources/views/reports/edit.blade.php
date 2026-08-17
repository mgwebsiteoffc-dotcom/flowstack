@extends('layouts.app')
@section('title', 'Edit report')
@section('content')
<form method="POST" action="{{ route('reports.update', $report) }}" class="max-w-4xl space-y-6">
    @csrf
    @method('PATCH')
    <x-card title="Report details" icon="pencil-square">
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                <input type="text" name="title" value="{{ old('title', $report->title) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Client *</label>
                <select name="client_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id', $report->client_id) == $client->id ? 'selected' : '' }}>{{ $client->company_name }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Report type</label>
                <select name="report_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach (['weekly', 'monthly', 'quarterly', 'custom'] as $t)
                        <option value="{{ $t }}" {{ old('report_type', $report->report_type) === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Period start</label>
                <input type="date" name="period_start" value="{{ old('period_start', $report->period_start->toDateString()) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Period end</label>
                <input type="date" name="period_end" value="{{ old('period_end', $report->period_end->toDateString()) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach (['draft', 'final', 'shared'] as $s)
                        <option value="{{ $s }}" {{ old('status', $report->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select></div>
        </div>
    </x-card>

    <x-card title="Metrics (JSON data, edit carefully)" icon="chart-bar">
        <textarea name="data_json" rows="10" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono">{{ old('data_json', json_encode($report->data, JSON_PRETTY_PRINT)) }}</textarea>
    </x-card>

    <x-card title="Commentary" icon="chat-bubble-left-right">
        <div class="space-y-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">What worked</label>
                <textarea name="insights" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('insights', $report->insights) }}</textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Areas to improve</label>
                <textarea name="recommendations" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('recommendations', $report->recommendations) }}</textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Next priorities</label>
                <textarea name="next_priorities" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('next_priorities', $report->next_priorities) }}</textarea></div>
        </div>
    </x-card>

    <div class="flex justify-end gap-3">
        <a href="{{ route('reports.show', $report) }}" class="px-5 py-2 rounded-lg text-sm text-gray-500 hover:bg-gray-100">Cancel</a>
        <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Save changes</button>
    </div>
</form>
@endsection
