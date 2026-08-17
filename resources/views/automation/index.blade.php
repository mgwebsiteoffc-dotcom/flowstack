@extends('layouts.app')
@section('title', 'Automation')
@section('breadcrumb', 'Automation')
@section('content')
<div class="flex items-center justify-between mb-5">
    <h2 class="text-lg font-bold text-gray-900">Automation rules</h2>
    <button x-data @click="$refs.ruleModal.showModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium">+ New rule</button>
</div>

<dialog id="rule-modal" x-ref="ruleModal" class="rounded-2xl shadow-2xl p-0 w-full max-w-2xl">
    <form method="POST" action="{{ route('automation.store') }}" class="p-6 space-y-4" x-data="ruleBuilder()" x-init="init()">
        @csrf
        <h3 class="font-semibold text-gray-900">New automation rule</h3>
        <input type="text" name="name" placeholder="Rule name *" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Trigger event *</label>
                <select name="trigger_event" x-model="trigger" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach (\App\Models\AutomationRule::TRIGGERS as $trigger)
                        <option value="{{ $trigger }}">{{ $trigger }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Delay (hours)</label>
                <input type="number" name="trigger_delay_hours" value="0" min="0" max="720" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Conditions</label>
            <template x-for="(c, i) in conditions" :key="i">
                <div class="flex gap-2 mb-2">
                    <select :name="'conditions[' + i + '][field]'" x-model="c.field" class="flex-1 rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                        @foreach (\App\Models\AutomationRule::CONDITION_FIELDS as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <select :name="'conditions[' + i + '][operator]'" x-model="c.operator" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                        <option value="equals">equals</option>
                        <option value="not_equals">not equals</option>
                        <option value="greater_than">greater than</option>
                        <option value="less_than">less than</option>
                        <option value="is_null">is null</option>
                        <option value="not_null">not null</option>
                    </select>
                    <input type="text" :name="'conditions[' + i + '][value]'" x-model="c.value" placeholder="Value" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                    <button type="button" @click="conditions.splice(i, 1)" class="text-red-400">✕</button>
                </div>
            </template>
            <button type="button" @click="conditions.push({ field: 'client_id', operator: 'equals', value: '' })" class="text-sm text-indigo-600">+ Add condition</button>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Actions *</label>
            <template x-for="(a, i) in actions" :key="i">
                <div class="bg-gray-50 rounded-lg p-3 mb-2">
                    <div class="flex gap-2 mb-2">
                        <select x-model="a.type" class="flex-1 rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                            @foreach (\App\Models\AutomationRule::ACTION_TYPES as $type)
                                <option value="{{ $type }}">{{ str_replace('_', ' ', $type) }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" :name="'actions[' + i + '][type]'" :value="a.type">
                        <button type="button" @click="actions.splice(i, 1)" class="text-red-400">✕</button>
                    </div>
                    <template x-if="a.type === 'send_notification'">
                        <div class="grid grid-cols-2 gap-2">
                            <select :name="'actions[' + i + '][params][target]'" x-model="a.target" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                                <option value="assigned_to">Assignee</option>
                                <option value="client_account_manager">Account manager</option>
                                <option value="creator">Task creator</option>
                                <option value="role">Role</option>
                            </select>
                            <input type="text" :name="'actions[' + i + '][params][message]'" x-model="a.message" placeholder="Message (use [variables])" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                        </div>
                    </template>
                    <template x-if="a.type === 'send_email'">
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" :name="'actions[' + i + '][params][subject]'" x-model="a.subject" placeholder="Subject" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                            <input type="text" :name="'actions[' + i + '][params][message]'" x-model="a.message" placeholder="Message" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                        </div>
                    </template>
                    <template x-if="a.type === 'create_task'">
                        <div class="grid grid-cols-3 gap-2">
                            <input type="text" :name="'actions[' + i + '][params][title]'" x-model="a.title" placeholder="Title ([lead_name], [task_title])" class="col-span-2 rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                            <input type="number" :name="'actions[' + i + '][params][due_days]'" x-model="a.due_days" placeholder="Due in days" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                        </div>
                    </template>
                    <template x-if="a.type === 'change_task_status'">
                        <select :name="'actions[' + i + '][params][status]'" x-model="a.status" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                            @foreach (\App\Models\Task::STATUSES as $s)
                                <option value="{{ $s }}">{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                            @endforeach
                        </select>
                    </template>
                    <template x-if="a.type === 'create_project_from_template'">
                        <select :name="'actions[' + i + '][params][template_id]'" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                            @foreach ($templates as $template)
                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                            @endforeach
                        </select>
                    </template>
                    <template x-if="a.type === 'post_comment'">
                        <input type="text" :name="'actions[' + i + '][params][text]'" x-model="a.message" placeholder="Comment text" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                    </template>
                    <template x-if="a.type === 'log_activity'">
                        <input type="text" :name="'actions[' + i + '][params][message]'" x-model="a.message" placeholder="Activity message" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                    </template>
                    <template x-if="a.type === 'assign_task'">
                        <input type="text" :name="'actions[' + i + '][params][user_id]'" x-model="a.user_id" placeholder="User ID" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                    </template>
                </div>
            </template>
            <button type="button" @click="actions.push({ type: 'send_notification', target: 'assigned_to', message: '', subject: '', title: '', status: 'done', due_days: 0, user_id: '' })" class="text-sm text-indigo-600">+ Add action</button>
        </div>

        <div class="flex gap-3 justify-end">
            <button type="button" @click="$refs.ruleModal.close()" class="px-4 py-2 text-sm text-gray-500">Cancel</button>
            <button class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg">Create rule</button>
        </div>
    </form>
</dialog>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-4">
        @forelse ($rules as $rule)
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div>
                        <div class="font-semibold text-gray-900 flex items-center gap-2">
                            {{ $rule->name }}
                            <span class="text-xs bg-gray-100 rounded-full px-2 py-0.5 text-gray-500">{{ $rule->trigger_event }}</span>
                            @if ($rule->trigger_delay_hours > 0)
                                <span class="text-xs bg-amber-100 text-amber-700 rounded-full px-2 py-0.5">delay {{ $rule->trigger_delay_hours }}h</span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-400 mt-1">
                            {{ count($rule->conditions ?? []) }} condition(s) · {{ count($rule->actions ?? []) }} action(s) · ran {{ $rule->run_count }}× · {{ $rule->logs_count }} logs
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('automation.test', $rule) }}" class="flex items-center gap-1">
                            @csrf
                            <select name="subject_type" class="rounded border border-gray-300 px-1.5 py-1 text-xs">
                                <option value="task">Task</option>
                                <option value="lead">Lead</option>
                                <option value="client">Client</option>
                            </select>
                            <input type="number" name="subject_id" placeholder="ID" required class="w-16 rounded border border-gray-300 px-1.5 py-1 text-xs">
                            <button class="text-xs text-indigo-600">Test</button>
                        </form>
                        <form method="POST" action="{{ route('automation.toggle', $rule) }}">@csrf
                            <button class="text-xs px-2 py-1 rounded-full {{ $rule->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $rule->is_active ? '● Enabled' : '○ Disabled' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('automation.destroy', $rule) }}" onsubmit="return confirm('Delete this rule?')">@csrf @method('DELETE')
                            <button class="text-xs text-red-400">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <x-empty-state icon="⚡" title="No automation rules" message="Create rules to automate notifications, task creation and more." />
        @endforelse
    </div>

    <x-card title="Recent executions" icon="🕓">
        <div class="divide-y divide-gray-50">
            @forelse ($recentLogs as $log)
                <div class="py-2">
                    <div class="flex items-center gap-2 text-sm">
                        <span class="w-5 text-center">{{ $log->status === 'success' ? '✅' : ($log->status === 'failed' ? '❌' : '⏭️') }}</span>
                        <span class="text-gray-800 truncate flex-1">{{ $log->rule?->name }}</span>
                    </div>
                    @if ($log->error_message)
                        <div class="text-xs text-gray-400 mt-0.5 ml-7 truncate">{{ $log->error_message }}</div>
                    @endif
                    <div class="text-xs text-gray-400 ml-7">{{ $log->created_at?->diffForHumans() }}</div>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-4">No executions yet.</p>
            @endforelse
        </div>
    </x-card>
</div>
@endsection

@push('scripts')
<script>
function ruleBuilder() {
    return {
        trigger: 'task.created',
        conditions: [],
        actions: [],
        init() {
            this.actions.push({ type: 'send_notification', target: 'assigned_to', message: '', subject: '', title: '', status: 'done', due_days: 0, user_id: '' });
        }
    }
}
</script>
@endpush
