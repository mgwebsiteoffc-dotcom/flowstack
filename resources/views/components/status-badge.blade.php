@props(['status', 'type' => 'task'])
@php
    $map = [
        'task' => [
            'backlog' => 'bg-gray-100 text-gray-700', 'todo' => 'bg-gray-100 text-gray-700',
            'in_progress' => 'bg-blue-100 text-blue-700', 'in_review' => 'bg-purple-100 text-purple-700',
            'waiting_approval' => 'bg-amber-100 text-amber-700', 'done' => 'bg-green-100 text-green-700',
            'blocked' => 'bg-red-100 text-red-700',
        ],
        'invoice' => [
            'draft' => 'bg-gray-100 text-gray-600', 'sent' => 'bg-blue-100 text-blue-700',
            'paid' => 'bg-green-100 text-green-700', 'overdue' => 'bg-red-100 text-red-700',
            'cancelled' => 'bg-gray-200 text-gray-500',
        ],
        'lead' => [
            'active' => 'bg-blue-100 text-blue-700', 'won' => 'bg-green-100 text-green-700',
            'lost' => 'bg-red-100 text-red-700', 'archived' => 'bg-gray-100 text-gray-600',
        ],
        'project' => [
            'active' => 'bg-green-100 text-green-700', 'on_hold' => 'bg-amber-100 text-amber-700',
            'completed' => 'bg-blue-100 text-blue-700', 'cancelled' => 'bg-red-100 text-red-700',
        ],
        'client' => [
            'active' => 'bg-green-100 text-green-700', 'inactive' => 'bg-gray-100 text-gray-600',
            'onboarding' => 'bg-amber-100 text-amber-700', 'offboarding' => 'bg-red-100 text-red-700',
        ],
    ];
    $class = $map[$type][$status] ?? 'bg-gray-100 text-gray-700';
    $label = ucwords(str_replace('_', ' ', (string) $status));
@endphp
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $class }}">{{ $label }}</span>
