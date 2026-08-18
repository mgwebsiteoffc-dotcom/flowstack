<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class ClientService extends Model
{
 use TenantScoped;

 public const TYPES = [
 'digital_marketing' => 'Digital Marketing',
 'shopify_operations' => 'Shopify Operations',
 'social_media' => 'Social Media Management',
 'website_management' => 'Website Management',
 'ai_automation' => 'AI Automation',
 ];

 protected $fillable = [
 'tenant_id', 'client_id', 'service_type', 'monthly_price', 'is_active',
 ];

 protected $casts = [
 'monthly_price' => 'decimal:2',
 'is_active' => 'boolean',
 ];

 public function client()
 {
 return $this->belongsTo(Client::class);
 }

 public function getTypeLabelAttribute(): string
 {
 return self::TYPES[$this->service_type] ?? $this->service_type;
 }
}
