@extends('layouts.auth')
@section('title', 'Pricing')
@section('content')
    <div class="max-w-3xl mx-auto py-6">
        <h1 class="text-2xl font-bold text-gray-900 text-center">Simple pricing for growing agencies</h1>
        <p class="text-sm text-gray-500 text-center mt-1">Start free for 14 days. Upgrade anytime.</p>
        <div class="grid sm:grid-cols-3 gap-4 mt-8">
            @foreach ($plans as $plan)
                <div class="rounded-2xl border {{ $plan->slug === 'professional' ? 'border-indigo-600 ring-2 ring-indigo-600' : 'border-gray-200' }} p-6 bg-white">
                    <h3 class="font-bold text-gray-900">{{ $plan->name }}</h3>
                    <div class="mt-2">
                        <span class="text-3xl font-black">₹{{ number_format($plan->price_monthly) }}</span>
                        <span class="text-sm text-gray-500">/month</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">or ₹{{ number_format($plan->price_yearly) }}/year</p>
                    <ul class="mt-4 space-y-2 text-sm text-gray-600">
                        @foreach (($plan->features ?? []) as $feature)
                            <li class="flex gap-2"><span class="text-green-600">✓</span>{{ $feature }}</li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register') }}" class="mt-6 block text-center rounded-lg py-2 text-sm font-medium {{ $plan->slug === 'professional' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">
                        Start free trial
                    </a>
                </div>
            @endforeach
        </div>
        <p class="text-center text-xs text-gray-400 mt-8"><a href="{{ route('home') }}" class="hover:text-gray-600">← Back to home</a></p>
    </div>
@endsection
