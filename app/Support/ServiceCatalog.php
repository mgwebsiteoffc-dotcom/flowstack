<?php

namespace App\Support;

use App\Models\ClientService;
use App\Models\MasterItem;
use Illuminate\Support\Str;

/**
 * Service type catalog: built-in defaults + tenant-defined custom types
 * (stored in master_items with type=service_type, meta.slug = slug).
 */
class ServiceCatalog
{
    public static function all(): array
    {
        $defaults = ClientService::TYPES;

        $customs = MasterItem::query()
            ->where('type', 'service_type')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->meta['slug'] ?? Str::slug($item->name) => $item->name])
            ->toArray();

        return array_merge($defaults, $customs);
    }

    public static function slugs(): array
    {
        return array_keys(self::all());
    }
}
