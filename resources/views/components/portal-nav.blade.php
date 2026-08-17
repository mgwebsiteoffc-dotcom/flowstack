<nav class="space-y-1 text-sm">
    <a href="{{ route('portal.dashboard') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('portal.dashboard') ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">📊 Dashboard</a>
    <a href="{{ route('portal.projects') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('portal.projects*') ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">📁 Projects</a>
    <a href="{{ route('portal.reports') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('portal.reports*') ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">📈 Reports</a>
    <a href="{{ route('portal.approvals') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('portal.approvals') ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">✅ Approvals</a>
    <a href="{{ route('portal.requests') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('portal.requests*') ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">📨 Requests</a>
    <a href="{{ route('portal.invoices') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('portal.invoices*') ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">🧾 Invoices</a>
    <a href="{{ route('portal.files') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('portal.files*') ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">📎 Files</a>
</nav>
