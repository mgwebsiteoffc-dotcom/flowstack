<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    use TenantScoped;

    protected $fillable = [
        'tenant_id', 'client_id', 'lead_id', 'proposal_number', 'title', 'status',
        'valid_until', 'subtotal', 'tax_rate', 'tax_amount', 'discount_type',
        'discount_value', 'discount_amount', 'total_amount', 'currency',
        'notes', 'terms', 'sent_at', 'created_by',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'sent_at' => 'datetime',
    ];

    public const STATUSES = ['draft', 'sent', 'accepted', 'rejected'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function items()
    {
        return $this->hasMany(ProposalItem::class)->orderBy('order_index');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
