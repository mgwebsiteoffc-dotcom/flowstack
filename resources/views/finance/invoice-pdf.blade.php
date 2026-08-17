<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .logo { font-size: 22px; font-weight: 900; }
        .logo span { color: #4f46e5; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .meta { color: #6b7280; font-size: 11px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table.items th { background: #f3f4f6; text-align: left; padding: 8px; font-size: 11px; text-transform: uppercase; }
        table.items td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
        .totals { margin-left: auto; width: 280px; margin-top: 20px; }
        .totals div { display: flex; justify-content: space-between; padding: 3px 0; }
        .totals .grand { font-weight: 900; font-size: 14px; border-top: 2px solid #111827; margin-top: 5px; padding-top: 8px; }
        .bank { margin-top: 30px; padding: 12px; background: #f9fafb; font-size: 11px; color: #374151; }
        .footer { margin-top: 40px; font-size: 10px; color: #9ca3af; text-align: center; }
        .notes { margin-top: 20px; font-size: 11px; color: #4b5563; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="logo">Agency<span>OS</span> — {{ $agencyName }}</div>
            <div class="meta">{{ $agencyAddress }}</div>
            <div class="meta">{{ $agencyEmail }} {{ $agencyPhone }}</div>
        </div>
        <div style="text-align: right">
            <h1>INVOICE</h1>
            <div class="meta">#{{ $invoice->invoice_number }}</div>
            <div class="meta">Issued: {{ $invoice->issue_date->format('d M Y') }}</div>
            <div class="meta">Due: {{ $invoice->due_date->format('d M Y') }}</div>
            <div class="meta" style="margin-top:8px">Bill To:</div>
            <div style="font-weight:600">{{ $invoice->client->company_name }}</div>
            <div class="meta">{{ $invoice->client->address }}</div>
            <div class="meta">{{ $invoice->client->gstin ? 'GSTIN: '.$invoice->client->gstin : '' }}</div>
        </div>
    </div>

    <table class="items">
        <thead>
            <tr><th>Description</th><th style="text-align:right">Qty</th><th style="text-align:right">Rate</th><th style="text-align:right">Tax</th><th style="text-align:right">Amount</th></tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td style="text-align:right">{{ $item->quantity }}</td>
                    <td style="text-align:right">{{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align:right">{{ $item->tax_rate }}%</td>
                    <td style="text-align:right">{{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div><span>Subtotal</span><span>{{ number_format($invoice->subtotal, 2) }}</span></div>
        @if ($invoice->discount_amount > 0)
            <div><span>Discount</span><span>-{{ number_format($invoice->discount_amount, 2) }}</span></div>
        @endif
        <div><span>Tax ({{ $invoice->tax_rate }}%)</span><span>{{ number_format($invoice->tax_amount, 2) }}</span></div>
        <div class="grand"><span>Total ({{ $invoice->currency }})</span><span>{{ number_format($invoice->total_amount, 2) }}</span></div>
    </div>

    @php
    $agency = $invoice->tenant ?? app('currentTenant');
    $agencyName = $agency?->name ?? config('app.name');
    $agencyAddress = $agency?->address ?? '';
    $agencyEmail = $agency?->email ?? '';
    $agencyPhone = $agency?->phone ?? '';
    $settings = $agency?->settings ?? [];
@endphp
    @if (! empty($settings['bank_name']))
        <div class="bank">
            <strong>Bank details:</strong> {{ $settings['bank_name'] }} · A/C {{ $settings['bank_account_number'] }} ·
            IFSC {{ $settings['bank_ifsc'] }} · Beneficiary: {{ $settings['bank_beneficiary'] }}
        </div>
    @endif

    @if ($invoice->notes)
        <div class="notes"><strong>Notes:</strong> {{ $invoice->notes }}</div>
    @endif
    @if ($invoice->terms)
        <div class="notes"><strong>Terms:</strong> {{ $invoice->terms }}</div>
    @endif

    <div class="footer">{{ $settings['invoice_footer'] ?? 'Thank you for your business!' }}</div>
</body>
</html>
