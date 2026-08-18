@extends('layouts.site')
@section('title', 'Integrations')
@php
    $seo = [
        'title' => 'Integrations — Agency OS',
        'description' => 'Agency OS connects with Lead365 for leads, BikriBook for invoicing and Razorpay for payments.',
        'jsonLd' => [['@context' => 'https://schema.org', '@type' => 'CollectionPage', 'name' => 'Agency OS integrations', 'url' => url()->current()]],
    ];
@endphp
@section('content')
<header class="max-w-4xl mx-auto px-6 pt-16 pb-10 text-center">
    <h1 class="text-4xl sm:text-5xl font-black tracking-tight">Integrations that plug into your stack</h1>
    <p class="text-gray-500 mt-4 text-lg">Leads, invoicing and payments — connected automatically.</p>
</header>

<section class="max-w-7xl mx-auto px-6 pb-20 grid md:grid-cols-3 gap-6">
    @foreach ([
        ['link','Lead365','Inbound leads','Lead365 sends leads, stage changes, wins and losses to your pipeline in real time via webhooks.','Connect'],
        ['receipt','BikriBook','Outbound invoicing','Create GST invoices locally and sync to BikriBook for sending, PDFs and payment status.','Connect'],
        ['credit-card','Razorpay','Payments','Accept plan payments securely with Razorpay checkout and signature-verified callbacks.','Connect'],
        ['device-phone-mobile','Meta Ads','Lead capture','Meta lead events create leads automatically with campaign, ad and form attribution.','Connect'],
        ['envelope','Email','Notifications','Transactional emails for assignments, overdue tasks, invoices, reports and digests.','Connect'],
        ['clock','Scheduler','Automation','Overdue checks, recurring tasks, digests and syncs run on a built-in schedule.','Connect'],
    ] as [$icon,$name,$tag,$desc,$cta])
        <div class="rounded-2xl border border-gray-100 p-7 hover:shadow-lg transition">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center"><x-icon :name="$icon" class="w-5 h-5" /></div>
                <div><div class="font-bold">{{ $name }}</div><div class="text-xs text-indigo-600">{{ $tag }}</div></div>
            </div>
            <p class="text-sm text-gray-500">{{ $desc }}</p>
            <a href="{{ route('register') }}" class="inline-block mt-4 text-sm text-indigo-600 font-semibold hover:underline">{{ $cta }} →</a>
        </div>
    @endforeach
</section>
@endsection
