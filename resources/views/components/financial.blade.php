@props(['masked' => '••••'])
@if (auth()->user()->canViewFinancials())
    {{ $slot }}
@else
    <span class="text-gray-400 select-none" title="Billing information is hidden">{{ $masked }}</span>
@endif
