<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <script src="https://cdn.tailwindcss.com"></script>
    <x-brand-head />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <x-seo title="Contact us" description="Talk to the Task365 team about switching your agency to one operating system." />
    <x-tracking placement="head" />
</head>
<body class="bg-white text-gray-900 antialiased">
    <x-tracking placement="body" />

    @include('components.site-nav')

    <header class="max-w-3xl mx-auto px-6 pt-16 pb-10 text-center">
        <h1 class="text-4xl font-black tracking-tight">Let's talk</h1>
        <p class="text-gray-500 mt-3">Questions about Task365, onboarding your team, or switching from another tool? We reply within one business day.</p>
    </header>

    <div class="max-w-5xl mx-auto px-6 pb-20 grid md:grid-cols-5 gap-10">
        <div class="md:col-span-2 space-y-5">
            @foreach ([
                ['envelope','Email us','hello@task365.test','For sales and general enquiries'],
                ['chat-bubble-left-right','Live demo','Book a 20-min walkthrough','See how Task365 fits your agency'],
                ['clock','Response time','Under 24 hours','Mon-Fri, IST business hours'],
            ] as [$icon,$title,$value,$sub])
                <div class="flex gap-4 bg-gray-50 rounded-2xl p-5">
                    <span class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0"><x-icon :name="$icon" class="w-5 h-5" /></span>
                    <div>
                        <div class="font-semibold">{{ $title }}</div>
                        <div class="text-indigo-600 font-medium text-sm">{{ $value }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ $sub }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="md:col-span-3">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-lg shadow-gray-100 p-8">
                @if (session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-6 text-sm">{{ session('success') }}</div>
                @endif
                <form method="POST" action="{{ route('contact.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Your name *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Work email *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Company</label>
                        <input type="text" name="company" value="{{ old('company') }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">How can we help? *</label>
                        <textarea name="message" rows="5" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">{{ old('message') }}</textarea>
                    </div>
                    <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-3 text-sm font-semibold shadow-lg shadow-indigo-200">Send message</button>
                </form>
            </div>
        </div>
    </div>

    @include('components.site-footer')
</body>
</html>
