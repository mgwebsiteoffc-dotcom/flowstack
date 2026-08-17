@props(['target' => 'client_id', 'label' => ''])
@php $services = \App\Support\ServiceCatalog::all(); @endphp
<div x-data="quickClient(@js($target))" class="inline-flex items-center">
    @if ($label)<span class="text-xs text-gray-400 mr-1">{{ $label }}</span>@endif
    <button type="button" @click="open = true" title="Quick add client"
            class="w-8 h-8 rounded-lg border border-dashed border-indigo-300 text-indigo-500 hover:bg-indigo-50 flex items-center justify-center">
        <x-icon name="plus" class="w-4 h-4" />
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-black/40" @click="open = false"></div>
        <div class="relative min-h-full flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900">Quick add client</h3>
                    <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600 text-xl">×</button>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Company name *</label>
                        <input type="text" x-model="name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact name</label>
                            <input type="text" x-model="contactName" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact email</label>
                            <input type="email" x-model="contactEmail" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Services</label>
                        <div class="grid grid-cols-2 gap-1.5 max-h-36 overflow-y-auto">
                            <template x-for="svc in services" :key="svc.slug">
                                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                    <input type="checkbox" :value="svc.slug" x-model="selectedServices" class="rounded">
                                    <span x-text="svc.name" class="truncate"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                    <p x-show="error" x-text="error" class="text-xs text-red-600"></p>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="open = false" class="px-4 py-2 text-sm text-gray-500">Cancel</button>
                        <button type="button" @click="submit()" :disabled="saving || !name"
                                class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg disabled:bg-gray-300" x-text="saving ? 'Creating…' : 'Create client'"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function quickClient(target) {
    return {
        open: false,
        name: '',
        contactName: '',
        contactEmail: '',
        services: @js(array_map(fn ($k, $v) => ['slug' => $k, 'name' => $v], array_keys($services), array_values($services))),
        selectedServices: [],
        saving: false,
        error: '',
        async submit() {
            if (!this.name || this.saving) return;
            this.saving = true;
            this.error = '';
            try {
                const res = await fetch(@js(route('clients.store')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        company_name: this.name,
                        contact_name: this.contactName,
                        contact_email: this.contactEmail,
                        services: this.selectedServices
                    })
                });
                if (!res.ok) {
                    this.error = 'Could not create the client. Check the details and try again.';
                    return;
                }
                // After redirect, the final URL is /clients/{id} - grab the id.
                const idMatch = res.url.match(/\/clients\/(\d+)/);
                const sel = document.querySelector('select[name="' + target + '"]');
                if (sel && idMatch) {
                    const opt = document.createElement('option');
                    opt.value = idMatch[1];
                    opt.text = this.name;
                    opt.selected = true;
                    sel.appendChild(opt);
                    sel.dispatchEvent(new Event('change'));
                }
                this.open = false;
                this.name = ''; this.contactName = ''; this.contactEmail = ''; this.selectedServices = [];
            } catch (e) {
                this.error = 'Network error - please try again.';
            } finally {
                this.saving = false;
            }
        }
    }
}
</script>
