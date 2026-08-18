@props(['invoice'])
@if ($invoice->bikribook_sync_status === 'not_synced')
    <span class="inline-flex items-center gap-1 text-xs text-gray-500 bg-gray-100 px-2.5 py-0.5 rounded-full">Not synced</span>
@elseif ($invoice->bikribook_sync_status === 'synced')
    <span class="inline-flex items-center gap-1 text-xs text-green-700 bg-green-100 px-2.5 py-0.5 rounded-full" title="Synced {{ $invoice->bikribook_last_synced_at?->diffForHumans() }}">
        <x-icon name="check-circle" class="w-4 h-4 inline-block" /> {{ $invoice->bikribook_invoice_number ?? 'Synced' }}
    </span>
@elseif ($invoice->bikribook_sync_status === 'failed')
    <span class="inline-flex items-center gap-1 text-xs text-red-700 bg-red-100 px-2.5 py-0.5 rounded-full">
        <x-icon name="x-circle" class="w-4 h-4 inline-block" /> Sync failed
        <form method="POST" action="{{ route('finance.invoices.sync', $invoice) }}" class="inline">@csrf
            <button class="underline hover:text-red-900">Retry</button>
        </form>
    </span>
@else
    <span class="inline-flex items-center gap-1 text-xs text-blue-700 bg-blue-100 px-2.5 py-0.5 rounded-full">
        <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        Syncing…
    </span>
@endif
