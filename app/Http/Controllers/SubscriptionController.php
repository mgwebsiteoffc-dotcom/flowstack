<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
 public function upgrade()
 {
 $tenant = \App\Support\CurrentTenant::get();
 $plans = Plan::query()->where('is_active', true)->orderBy('price_monthly')->get();

 return view('subscription.upgrade', compact('tenant', 'plans'));
 }

 /**
 * Creates a Razorpay order and renders the checkout page.
 */
 public function checkout(Request $request)
 {
 $tenant = \App\Support\CurrentTenant::get();

 $validated = $request->validate([
 'plan_id' => ['required', 'exists:plans,id'],
 'cycle' => ['required', 'in:monthly,yearly'],
 ]);

 $plan = Plan::findOrFail($validated['plan_id']);
 $amount = $validated['cycle'] === 'yearly' ? $plan->price_yearly : $plan->price_monthly;

 if ($amount <= 0) {
 return back()->with('error', 'This plan has no price configured.');
 }

 try {
 $api = new \Razorpay\Api\Api(config('services.razorpay.key'), config('services.razorpay.secret'));

 $order = $api->order->create([
 'amount' => (int) round($amount * 100),
 'currency' => 'INR',
 'receipt' => 'tenant_'.$tenant->id.'_'.now()->timestamp,
 'notes' => ['tenant_id' => $tenant->id, 'plan' => $plan->slug, 'cycle' => $validated['cycle']],
 ]);

 $orderId = $order['id'];

 $subscription = Subscription::create([
 'tenant_id' => $tenant->id,
 'plan_id' => $plan->id,
 'status' => 'trial',
 'billing_cycle' => $validated['cycle'],
 'amount' => $amount,
 'started_at' => now(),
 'payment_gateway' => 'razorpay',
 'gateway_subscription_id' => $orderId,
 ]);

 return view('subscription.checkout', compact('tenant', 'plan', 'orderId', 'amount', 'subscription'));
 } catch (\Throwable $e) {
 logger()->error('Razorpay order creation failed', ['tenant_id' => $tenant->id, 'error' => $e->getMessage()]);

 return back()->with('error', 'Could not reach the payment gateway. Please try again.');
 }
 }

 /**
 * Cancel the active subscription (services continue until the paid period ends).
 */
 public function cancel(Request $request)
 {
 $tenant = \App\Support\CurrentTenant::get();

 $subscription = Subscription::where('tenant_id', $tenant->id)
 ->where('status', 'active')
 ->latest()
 ->first();

 if (! $subscription) {
 return back()->with('info', 'No active subscription to cancel.');
 }

 $subscription->update([
 'status' => 'cancelled',
 'cancelled_at' => now(),
 ]);

 return back()->with('success', 'Subscription cancelled. Access continues until '.$subscription->expires_at?->toFormattedDateString().'.');
 }

 /**
 * Verifies the Razorpay payment signature and activates the subscription.
 */
 public function callback(Request $request)
 {
 $validated = $request->validate([
 'razorpay_order_id' => ['required'],
 'razorpay_payment_id' => ['required'],
 'razorpay_signature' => ['required'],
 ]);

 $tenant = \App\Support\CurrentTenant::get();

 $subscription = Subscription::where('tenant_id', $tenant->id)
 ->where('gateway_subscription_id', $validated['razorpay_order_id'])
 ->latest()
 ->first();

 if (! $subscription) {
 return redirect()->route('upgrade')->with('error', 'Order not found.');
 }

 try {
 $api = new \Razorpay\Api\Api(config('services.razorpay.key'), config('services.razorpay.secret'));

 $api->utility->verifyPaymentSignature([
 'razorpay_order_id' => $validated['razorpay_order_id'],
 'razorpay_payment_id' => $validated['razorpay_payment_id'],
 'razorpay_signature' => $validated['razorpay_signature'],
 ]);
 } catch (\Throwable $e) {
 logger()->error('Razorpay signature verification failed', ['tenant_id' => $tenant->id, 'error' => $e->getMessage()]);

 return redirect()->route('upgrade')->with('error', 'Payment verification failed.');
 }

 $cycleMonths = $subscription->billing_cycle === 'yearly' ? 12 : 1;

 $subscription->update([
 'status' => 'active',
 'started_at' => now(),
 'expires_at' => now()->addMonths($cycleMonths),
 ]);

 SubscriptionPayment::create([
 'tenant_id' => $tenant->id,
 'subscription_id' => $subscription->id,
 'amount' => $subscription->amount,
 'currency' => 'INR',
 'status' => 'paid',
 'gateway_payment_id' => $validated['razorpay_payment_id'],
 'paid_at' => now(),
 ]);

 $tenant->update([
 'plan_id' => $subscription->plan_id,
 'is_trial' => false,
 'plan_started_at' => now(),
 'plan_expires_at' => now()->addMonths($cycleMonths),
 'max_users' => $subscription->plan?->max_users,
 'max_clients' => $subscription->plan?->max_clients,
 ]);

 return redirect()->route('dashboard')->with('success', 'Payment successful! Your plan is now active.');
 }
}
