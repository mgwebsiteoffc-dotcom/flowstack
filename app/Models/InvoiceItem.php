<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
 use TenantScoped;

 protected $fillable = [
 'tenant_id', 'invoice_id', 'description', 'quantity', 'unit_price',
 'tax_rate', 'tax_amount', 'total', 'order_index',
 ];

 protected $casts = [
 'quantity' => 'decimal:2',
 'unit_price' => 'decimal:2',
 'tax_rate' => 'decimal:2',
 'tax_amount' => 'decimal:2',
 'total' => 'decimal:2',
 ];

 public function invoice()
 {
 return $this->belongsTo(Invoice::class);
 }
}
