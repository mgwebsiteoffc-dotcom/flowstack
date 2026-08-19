@auth
@php
    $user = auth()->user();
    $unread = $user->unreadNotifications()->count();
    $notifications = $user->notifications()->latest()->limit(10)->get();
    $runningEntry = \App\Models\TimeEntry::where('user_id', $user->id)->where('is_running', true)->first();
@endphp
<header class="bg-white border-b border-gray-200 h-16 flex items-center px-4 sm:px-6 gap-4">
    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-800 text-xl"><x-icon name="menu" class="w-4 h-4 inline-block" /></button>

    <div class="hidden md:block text-sm text-gray-500">
        @yield('breadcrumb', '')
    </div>

    <!-- Global search -->
    <form action="{{ route('search') }}" method="GET" class="hidden sm:block flex-1 max-w-md ml-auto">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search clients, tasks, leads…"
               class="w-full bg-gray-100 rounded-lg px-4 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </form>

    <!-- Timer widget -->
    <div x-data="timerWidget()" x-init="init()" class="relative">
        <template x-if="running">
            <div class="flex items-center gap-2 bg-red-50 text-red-700 rounded-full px-3 py-1.5 text-sm">
                <span class="animate-pulse"><x-icon name="circle" class="w-4 h-4 inline-block" /></span>
                <span x-text="taskTitle" class="max-w-[120px] truncate hidden sm:inline"></span>
                <span x-text="elapsed" class="font-mono tabular-nums"></span>
                <button @click="stop()" class="font-bold hover:text-red-900"><x-icon name="stop" class="w-4 h-4 inline-block" /></button>
            </div>
        </template>
        <template x-if="!running">
            <div class="relative">
                <button @click="open = !open" class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 rounded-full px-3 py-1.5 text-sm text-gray-700">
                    <span class="text-indigo-600"><x-icon name="play" class="w-4 h-4 inline-block" /></span> Start timer
                </button>
                <div x-show="open" x-cloak @click.outside="open = false"
                     class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-xl border border-gray-200 p-3 z-50">
                    <select x-model="taskId" class="w-full text-sm border rounded-lg px-2 py-1.5 mb-2">
                        <option value="">Select task…</option>
                        @foreach (\App\Models\Task::whereNotIn('status', ['done', 'cancelled'])->whereNull('parent_task_id')->orderBy('title')->limit(50)->get() as $task)
                            <option value="{{ $task->id }}">#{{ $task->id }} · {{ Str::limit($task->title, 40) }}</option>
                        @endforeach
                    </select>
                    <button @click="start()" :disabled="!taskId"
                            class="w-full bg-indigo-600 disabled:bg-gray-300 text-white rounded-lg py-1.5 text-sm font-medium">Start</button>
                </div>
            </div>
        </template>
    </div>

    <!-- Install app (desktop) -->
    <div x-data="{ installable: false }" x-init="window.addEventListener('app:installable', () => installable = true)">
        <button x-show="installable" x-cloak @click="window.installApp()"
                class="hidden md:inline-flex items-center gap-2 bg-indigo-50 text-indigo-700 rounded-lg px-3 py-1.5 text-sm font-medium hover:bg-indigo-100">
            <x-icon name="arrow-down-tray" class="w-4 h-4" /> Install app
        </button>
    </div>

    <!-- Notifications -->
    <div x-data="{ open: false }" class="relative">
        <button @click="open = !open" class="relative text-gray-500 hover:text-gray-800 text-xl p-1">
            <x-icon name="bell" class="w-4 h-4 inline-block" />
            @if ($unread > 0)
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[16px] h-4 px-0.5 flex items-center justify-center">{{ $unread > 9 ? '9+' : $unread }}</span>
            @endif
        </button>
        <div x-show="open" x-cloak @click.outside="open = false"
             class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-200 z-50">
            <div class="p-3 border-b flex justify-between items-center">
                <span class="font-semibold text-sm">Notifications</span>
                <form method="POST" action="{{ route('notifications.read-all') }}">@csrf
                    <button class="text-xs text-indigo-600 hover:underline">Mark all read</button>
                </form>
            </div>
            <div class="max-h-80 overflow-y-auto">
                @forelse ($notifications as $notification)
                    <a href="{{ route('notifications.read', $notification->id) }}"
                       class="block px-3 py-2.5 hover:bg-gray-50 border-b border-gray-100 {{ $notification->read_at ? '' : 'bg-indigo-50' }}">
                        <div class="text-sm font-medium text-gray-800">{{ $notification->data['title'] ?? 'Notification' }}</div>
                        <div class="text-xs text-gray-500 line-clamp-2">{{ $notification->data['body'] ?? '' }}</div>
                        <div class="text-[10px] text-gray-400 mt-0.5">{{ $notification->created_at->diffForHumans() }}</div>
                    </a>
                @empty
                    <div class="p-6 text-center text-sm text-gray-400">No notifications yet</div>
                @endforelse
            </div>
            <a href="{{ route('notifications.index') }}" class="block p-2 text-center text-xs text-indigo-600 hover:bg-gray-50 rounded-b-xl">View all</a>
        </div>
    </div>

    <x-user-avatar :user="$user" size="sm" />
</header>
@endauth

@push('scripts')
<script>
function timerWidget() {
    return {
        open: false,
        running: {{ $runningEntry ? 'true' : 'false' }},
        taskId: {{ $runningEntry?->task_id ?? 'null' }},
        taskTitle: {{ $runningEntry ? json_encode(Str::limit($runningEntry->task?->title ?? 'Task', 40)) : "''" }},
        startedAt: {{ $runningEntry ? $runningEntry->started_at->timestamp : 'null' }},
        elapsed: '00:00:00',
        timer: null,
        init() {
            if (this.running && this.startedAt) {
                this.tick();
                this.timer = setInterval(() => this.tick(), 1000);
            }
        },
        tick() {
            const diff = Math.max(0, Math.floor(Date.now() / 1000) - this.startedAt);
            const h = String(Math.floor(diff / 3600)).padStart(2, '0');
            const m = String(Math.floor((diff % 3600) / 60)).padStart(2, '0');
            const s = String(diff % 60).padStart(2, '0');
            this.elapsed = h + ':' + m + ':' + s;
        },
        async start() {
            if (!this.taskId) return;
            const res = await fetch('{{ route('time.start') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ task_id: this.taskId })
            });
            if (res.ok) {
                this.running = true;
                this.startedAt = Math.floor(Date.now() / 1000);
                this.taskTitle = 'Task #' + this.taskId;
                this.open = false;
                this.tick();
                this.timer = setInterval(() => this.tick(), 1000);
            }
        },
        async stop() {
            const res = await fetch('{{ route('time.stop') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            if (res.ok) {
                this.running = false;
                clearInterval(this.timer);
                window.location.reload();
            }
        }
    }
}
</script>
@endpush
