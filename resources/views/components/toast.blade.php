<div x-data="{ toasts: [] }" x-init="
    window.addEventListener('toast', e => {
        toasts.push({ id: Date.now(), message: e.detail.message, type: e.detail.type || 'success' });
        setTimeout(() => toasts = toasts.filter(t => t.id !== Date.now()), 4000);
    });
" class="fixed bottom-4 right-4 z-50 space-y-2">
    <template x-for="toast in toasts" :key="toast.id">
        <div :class="toast.type === 'error' ? 'bg-red-600' : 'bg-gray-900'"
             class="text-white text-sm px-4 py-3 rounded-xl shadow-lg flex items-center gap-2"
             x-text="toast.message"></div>
    </template>
</div>
@if (session('success'))
    <script>window.dispatchEvent(new CustomEvent('toast', { detail: { message: @json(session('success')) } }));</script>
@endif
