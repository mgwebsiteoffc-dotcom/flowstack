{{-- Canonical public site footer — used on EVERY public page. --}}
<footer class="border-t border-gray-100">
    <div class="max-w-7xl mx-auto px-6 py-12 grid sm:grid-cols-4 gap-8">
        <div class="sm:col-span-2">
            <div class="text-xl font-black">Agency<span class="text-indigo-600">OS</span></div>
            <p class="text-sm text-gray-500 mt-2 max-w-xs">The operating system for modern agencies. Clients, projects, leads, invoices and reports in one place.</p>
        </div>
        <div>
            <div class="text-xs font-semibold text-gray-400 uppercase mb-3">Product</div>
            <div class="space-y-2 text-sm text-gray-600">
                <a href="{{ route('site.features') }}" class="block hover:text-gray-900">Features</a>
                <a href="{{ route('site.use-cases') }}" class="block hover:text-gray-900">Use cases</a>
                <a href="{{ route('site.integrations') }}" class="block hover:text-gray-900">Integrations</a>
                <a href="{{ route('tools.index') }}" class="block hover:text-gray-900">Free Tools</a>
                <a href="{{ route('pricing') }}" class="block hover:text-gray-900">Pricing</a>
            </div>
        </div>
        <div>
            <div class="text-xs font-semibold text-gray-400 uppercase mb-3">Company</div>
            <div class="space-y-2 text-sm text-gray-600">
                <a href="{{ route('site.company') }}" class="block hover:text-gray-900">About us</a>
                <a href="{{ route('site.resources') }}" class="block hover:text-gray-900">Resources</a>
                <a href="{{ route('blog.index') }}" class="block hover:text-gray-900">Blog</a>
                <a href="{{ route('site.faq') }}" class="block hover:text-gray-900">FAQ</a>
                <a href="{{ route('contact') }}" class="block hover:text-gray-900">Contact</a>
            </div>
        </div>
    </div>
    <div class="border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-6 py-5 flex flex-wrap items-center justify-between text-xs text-gray-400">
            <span>© {{ date('Y') }} Agency OS. All rights reserved.</span>
            <span>Made for agencies that ship.</span>
        </div>
    </div>
</footer>
