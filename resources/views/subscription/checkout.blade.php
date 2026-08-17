@extends('layouts.app')
@section('title', 'Checkout')
@section('content')
<div class="max-w-md mx-auto">
    <x-card title="Complete payment" icon="💳">
        <div class="text-sm text-gray-600 mb-4">
            <div class="flex justify-between py-1"><span>Plan</span><span class="font-medium">{{ $plan->name }}</span></div>
            <div class="flex justify-between py-1"><span>Billing</span><span class="font-medium">{{ $subscription->billing_cycle === 'yearly' ? 'Yearly' : 'Monthly' }}</span></div>
            <div class="flex justify-between py-1 border-t mt-2 pt-2"><span>Amount</span><span class="font-black text-lg">₹{{ number_format($amount) }}</span></div>
        </div>
        <button id="pay-btn" class="w-full bg-indigo-600 text-white rounded-lg py-2.5 text-sm font-medium">Pay ₹{{ number_format($amount) }}</button>
        <p class="text-xs text-gray-400 mt-3 text-center">Secure payments by Razorpay</p>
    </x-card>
</div>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('pay-btn').addEventListener('click', function () {
    const options = {
        key: @json(config('services.razorpay.key')),
        amount: {{ (int) round($amount * 100) }},
        currency: 'INR',
        name: @json(config('app.name')),
        description: @json($plan->name.' plan'),
        order_id: @json($orderId),
        handler: function (response) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = @json(route('subscription.checkout.callback'));
            const csrf = document.createElement('input');
            csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = @json(csrf_token());
            form.appendChild(csrf);
            for (const [k, v] of Object.entries(response)) {
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = k; input.value = v;
                form.appendChild(input);
            }
            document.body.appendChild(form);
            form.submit();
        },
        theme: { color: '#4f46e5' }
    };
    const rzp = new Razorpay(options);
    rzp.open();
});
</script>
@endpush
