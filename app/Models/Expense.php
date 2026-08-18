<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
 use TenantScoped;

 protected $fillable = [
 'tenant_id', 'client_id', 'category_id', 'title', 'description',
 'amount', 'expense_date', 'receipt_file', 'is_billable', 'added_by',
 ];

 protected $casts = [
 'amount' => 'decimal:2',
 'expense_date' => 'date',
 'is_billable' => 'boolean',
 ];

 public function client()
 {
 return $this->belongsTo(Client::class);
 }

 public function category()
 {
 return $this->belongsTo(ExpenseCategory::class, 'category_id');
 }

 public function addedBy()
 {
 return $this->belongsTo(User::class, 'added_by');
 }
}
