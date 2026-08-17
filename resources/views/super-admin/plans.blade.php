@extends('layouts.super-admin')
@section('title', 'Plans')
@section('content')
<h1 class="text-xl font-bold text-white mb-6">Plans</h1>

<div class="grid lg:grid-cols-3 gap-4 mb-8">
    @foreach ($plans as $plan)
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <div class="flex justify-between items-start">
                <h3 class="font-bold text-white">{{ $plan->name }}</h3>
                <span class="text-xs px-2 py-0.5 rounded-full {{ $plan->is_active ? 'bg-green-500/10 text-green-400' : 'bg-gray-700 text-gray-400' }}">{{ $plan->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            <div class="text-2xl font-black text-white mt-2">₹{{ number_format($plan->price_monthly) }}<span class="text-xs text-gray-500 font-normal">/mo</span></div>
            <div class="text-xs text-gray-500 mt-1">₹{{ number_format($plan->price_yearly) }}/year · {{ $plan->max_users ?? '∞' }} users · {{ $plan->max_clients ?? '∞' }} clients</div>
            <div class="text-xs text-gray-500 mt-1">{{ $plan->tenants_count }} tenants</div>
            <details class="mt-3">
                <summary class="text-xs text-indigo-400 cursor-pointer">Edit</summary>
                <form method="POST" action="{{ route('super-admin.plans.update', $plan) }}" class="mt-3 space-y-2">
                    @csrf @method('PUT')
                    <input type="text" name="name" value="{{ $plan->name }}" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5 text-sm text-white">
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" step="0.01" name="price_monthly" value="{{ $plan->price_monthly }}" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5 text-sm text-white">
                        <input type="number" step="0.01" name="price_yearly" value="{{ $plan->price_yearly }}" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5 text-sm text-white">
                        <input type="number" name="max_users" value="{{ $plan->max_users }}" placeholder="Max users" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5 text-sm text-white">
                        <input type="number" name="max_clients" value="{{ $plan->max_clients }}" placeholder="Max clients" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5 text-sm text-white">
                    </div>
                    <label class="flex items-center gap-2 text-xs text-gray-400"><input type="checkbox" name="is_active" value="1" class="rounded" {{ $plan->is_active ? 'checked' : '' }}> Active</label>
                    <button class="w-full bg-indigo-600 text-white rounded-lg py-1.5 text-sm">Save</button>
                </form>
            </details>
        </div>
    @endforeach
</div>

<details class="bg-gray-900 rounded-xl border border-gray-800 p-5">
    <summary class="font-semibold text-white cursor-pointer">+ Create new plan</summary>
    <form method="POST" action="{{ route('super-admin.plans.store') }}" class="mt-4 grid sm:grid-cols-2 gap-3">
        @csrf
        <input type="text" name="name" placeholder="Plan name *" required class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
        <input type="text" name="slug" placeholder="slug *" required class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
        <input type="number" step="0.01" name="price_monthly" placeholder="Monthly price *" required class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
        <input type="number" step="0.01" name="price_yearly" placeholder="Yearly price *" required class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
        <input type="number" name="max_users" placeholder="Max users" class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
        <input type="number" name="max_clients" placeholder="Max clients" class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
        <input type="number" name="max_storage_gb" placeholder="Storage (GB)" class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
        <label class="flex items-center gap-2 text-xs text-gray-400"><input type="checkbox" name="is_active" value="1" checked class="rounded"> Active</label>
        <div class="sm:col-span-2">
            <textarea name="features" rows="3" placeholder="Features (one per line)" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white"></textarea>
        </div>
        <button class="sm:col-span-2 bg-indigo-600 text-white rounded-lg py-2 text-sm">Create plan</button>
    </form>
</details>
@endsection
