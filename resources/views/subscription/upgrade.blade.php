@extends('layouts.app')
@section('title', 'Upgrade')
@section('content')
<div class="max-w-4xl mx-auto">
    @if ($tenant->is_trial)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 mb-6 text-center">
            <div class="text-sm text-amber-800 font-medium">
                ⏳ {{ $tenant->trialDaysRemaining() > 0 ? $tenant->trialDaysRemaining().' days remaining' : 'Your trial has ended' }} in your free trial.
                Upgrade to keep your workspace running.
            </div>
        </div>
    @endif

    <h1 class="text-2xl font-bold text-gray-900 text-center">Choose your plan</h1>
    <p class="text-sm text-gray-500 text-center mt-1">Billed via Razorpay. Cancel anytime.</p>

    <div class="grid md:grid-cols-3 gap-5 mt-8">
        @foreach ($plans as $plan)
            <form method="POST" action="{{ route('subscription.checkout') }}" class="rounded-2xl border {{ $plan->slug === 'professional' ? 'border-indigo-600 ring-2 ring-indigo-600' : 'border-gray-200' }} p-6 bg-white flex flex-col">
                @csrf
                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                <h3 class="font-bold text-gray-900">{{ $plan->name }}</h3>
                <div class="mt-2">
                    <span class="text-3xl font-black">₹{{ number_format($plan->price_monthly) }}</span>
                    <span class="text-sm text-gray-500">/month</span>
                </div>
                <ul class="mt-4 space-y-2 text-sm text-gray-600 flex-1">
                    @foreach (($plan->features ?? []) as $feature)
                        <li class="flex gap-2"><span class="text-green-600">✓</span>{{ $feature }}</li>
                    @endforeach
                </ul>
                <div class="mt-4 flex gap-2">
                    <button name="cycle" value="monthly" class="flex-1 rounded-lg py-2 text-sm font-medium {{ $plan->slug === 'professional' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-800' }}">Monthly</button>
                    <button name="cycle" value="yearly" class="flex-1 rounded-lg py-2 text-sm font-medium bg-gray-100 text-gray-800 hover:bg-gray-200" title="2 months free">Yearly</button>
                </div>
            </form>
        @endforeach
    </div>
</div>
@endsection
