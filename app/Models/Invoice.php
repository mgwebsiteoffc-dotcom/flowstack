<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes, TenantScoped;

    protected $fillable = [
        'tenant_id', 'client_id', 'invoice_number', 'bikribook_invoice_id',
        'bikribook_invoice_number', 'bikribook_pdf_url', 'bikribook_sync_status',
        'bikribook_last_synced_at', 'status', 'issue_date', 'due_date', 'subtotal',
        'tax_rate', 'tax_amount', 'discount_type', 'discount_value',
        'discount_amount', 'total_amount', 'paid_amount', 'payment_date',
        'payment_method', 'currency', 'notes', 'terms', 'sent_at', 'viewed_at',
        'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'datetime',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'bikribook_last_synced_at' => 'datetime',
    ];

    public const STATUSES = ['draft', 'sent', 'paid', 'overdue', 'cancelled'];
    public const BB_STATUSES = ['not_synced', 'synced', 'failed'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('order_index');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bikribookSyncLogs()
    {
        return $this->hasMany(BikriBookSyncLog::class)->latest();
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'sent')
            ->where('due_date', '<', now()->toDateString());
    }

    public function isFullyPaid(): bool
    {
        return (float) $this->paid_amount >= (float) $this->total_amount;
    }

    public function balanceDue(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->paid_amount);
    }
}
