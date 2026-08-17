@extends('layouts.app')
@section('title', 'Expenses')
@section('breadcrumb', 'Finance / Expenses')
@section('content')
<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <form method="GET" class="flex flex-wrap gap-2 text-sm">
        <select name="category_id" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
            <option value="">All categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
            @endforeach
        </select>
        <select name="client_id" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
            <option value="">All clients</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->company_name }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
        <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
        <button class="bg-gray-800 text-white px-4 py-1.5 rounded-lg">Filter</button>
    </form>
    <div class="flex gap-2 items-center">
        <span class="text-sm text-gray-500">Total: <strong>₹{{ number_format($total) }}</strong></span>
        <a href="{{ route('finance.expenses.index', array_merge(request()->query(), ['export' => 1])) }}" class="px-3 py-2 rounded-lg bg-white border border-gray-200 text-gray-600 text-sm"><x-icon name="arrow-down-tray" class="w-4 h-4 inline-block" /> Export</a>
        <button x-data @click="$refs.expenseModal.showModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium">+ Add expense</button>
    </div>
</div>

<dialog id="expense-modal" x-ref="expenseModal" class="rounded-2xl shadow-2xl p-0 w-full max-w-md">
    <form method="POST" action="{{ route('finance.expenses.store') }}" enctype="multipart/form-data" class="p-6 space-y-3">
        @csrf
        <h3 class="font-semibold text-gray-900 mb-2">Add expense</h3>
        <input type="text" name="title" placeholder="Title *" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <div class="grid grid-cols-2 gap-3">
            <input type="number" step="0.01" name="amount" placeholder="Amount *" required min="0.01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <input type="date" name="expense_date" value="{{ now()->toDateString() }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <select name="category_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">Category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="client_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">Client (optional)</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                @endforeach
            </select>
        </div>
        <textarea name="description" rows="2" placeholder="Description" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
        <label class="flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="is_billable" value="1" class="rounded"> Billable to client</label>
        <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf" class="text-sm">
        <div class="flex gap-3 justify-end pt-2">
            <button type="button" @click="$refs.expenseModal.close()" class="px-4 py-2 text-sm text-gray-500">Cancel</button>
            <button class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg">Save expense</button>
        </div>
    </form>
</dialog>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
            <tr><th class="px-4 py-3">Title</th><th class="px-4 py-3">Category</th><th class="px-4 py-3">Client</th><th class="px-4 py-3 text-right">Amount</th><th class="px-4 py-3">Date</th><th class="px-4 py-3">Added by</th><th class="px-4 py-3"></th></tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse ($expenses as $expense)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $expense->title }}</td>
                    <td class="px-4 py-3"><span class="text-xs bg-gray-100 rounded-full px-2 py-0.5">{{ $expense->category?->name ?? '—' }}</span></td>
                    <td class="px-4 py-3 text-gray-600">{{ $expense->client?->company_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-medium">₹{{ number_format($expense->amount) }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $expense->expense_date->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $expense->addedBy?->name }}</td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('finance.expenses.destroy', $expense) }}" onsubmit="return confirm('Delete this expense?')">@csrf @method('DELETE')
                            <button class="text-red-400 text-xs">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><x-empty-state icon="banknotes" title="No expenses" message="Record expenses to track profitability." /></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<x-pagination :paginator="$expenses" />
@endsection
