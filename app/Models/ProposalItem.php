<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class ProposalItem extends Model
{
    use TenantScoped;

    protected $fillable = [
        'tenant_id', 'proposal_id', 'description', 'quantity', 'unit_price', 'total', 'order_index',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }
}
