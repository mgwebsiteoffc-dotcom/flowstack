@auth
{{-- First-launch intro / splash slides: shown once per device, then remembered
     in localStorage. Gives the installed app a native on-boarding feel. --}}
<div x-data="appIntro()" x-init="init()" x-show="show" x-cloak
     class="fixed inset-0 z-[60] bg-indigo-600 flex flex-col items-center justify-center px-8 text-center">
    <div class="w-20 h-20 rounded-3xl bg-white/10 flex items-center justify-center mb-8">
        <x-icon name="chart-bar" class="w-10 h-10 text-white" />
    </div>

    <div class="relative w-full max-w-sm h-40 overflow-hidden">
        @foreach ([
            ['icon' => 'rocket-launch', 'title' => 'Welcome to Task365', 'text' => 'Run your agency on autopilot — clients, projects, tasks, leads and invoices in one place.'],
            ['icon' => 'check-circle', 'title' => 'Everything in one place', 'text' => 'Track work, assign tasks, manage leads and generate reports without the scattered spreadsheets.'],
            ['icon' => 'bell', 'title' => 'Stay notified anywhere', 'text' => 'Install this app on your phone and turn on notifications to never miss a task, deadline or comment.'],
        ] as $i => $slide)
            <div x-show="step === {{ $i }}" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0 opacity-100" x-transition:leave-end="-translate-x-full opacity-0"
                 class="absolute inset-0 flex flex-col items-center justify-center">
                <x-icon name="{{ $slide['icon'] }}" class="w-14 h-14 text-white/90 mb-4" />
                <h2 class="text-2xl font-bold text-white mb-2">{{ $slide['title'] }}</h2>
                <p class="text-indigo-100 text-sm leading-relaxed">{{ $slide['text'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Dots --}}
    <div class="flex gap-2 mt-6">
        @foreach ([0, 1, 2] as $i)
            <button @click="step = {{ $i }}" class="w-2.5 h-2.5 rounded-full transition {{ $i === 0 ? 'bg-white' : 'bg-white/40' }}" :class="step === {{ $i }} ? 'bg-white w-6' : 'bg-white/40'"></button>
        @endforeach
    </div>

    <div class="flex items-center gap-3 mt-10 w-full max-w-sm">
        <button @click="finish()" class="flex-1 py-3 rounded-xl text-white/80 text-sm font-medium hover:text-white">Skip</button>
        <template x-if="step < 2">
            <button @click="step++" class="flex-1 py-3 rounded-xl bg-white text-indigo-600 text-sm font-semibold">Next</button>
        </template>
        <template x-if="step === 2">
            <button @click="finish()" class="flex-1 py-3 rounded-xl bg-white text-indigo-600 text-sm font-semibold">Get started</button>
        </template>
    </div>
</div>
@endauth
@once
@push('scripts')
<script>
function appIntro() {
    return {
        step: 0,
        show: false,
        init() {
            if (!localStorage.getItem('task365_intro_seen')) {
                this.show = true;
                document.body.style.overflow = 'hidden';
            }
        },
        finish() {
            localStorage.setItem('task365_intro_seen', '1');
            this.show = false;
            document.body.style.overflow = '';
        }
    }
}
</script>
@endpush
@endonce
