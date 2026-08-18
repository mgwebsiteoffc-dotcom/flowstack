@extends('layouts.super-admin')
@section('title', 'Payments')
@section('content')
<h1 class="text-xl font-bold text-white mb-6">Payments</h1>
<div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-950 text-left text-xs text-gray-500 uppercase">
            <tr><th class="px-4 py-3">Date</th><th class="px-4 py-3">Tenant</th><th class="px-4 py-3 text-right">Amount</th><th class="px-4 py-3">Gateway ID</th><th class="px-4 py-3">Status</th></tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
            @forelse ($payments as $payment)
                <tr>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $payment->paid_at?->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3 text-gray-200">{{ $payment->tenant?->name }}</td>
                    <td class="px-4 py-3 text-right text-green-400 font-bold">₹{{ number_format($payment->amount) }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $payment->gateway_payment_id }}</td>
                    <td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded-full bg-green-500/10 text-green-400 capitalize">{{ $payment->status }}</span></td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-10 text-center text-gray-500">No payments yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $payments->links() }}</div>
@endsection
