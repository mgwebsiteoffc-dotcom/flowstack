<?php

namespace App\Support;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Role -> menu visibility mapping, configured by the super admin
 * (/super-admin/roles). The map is stored in platform_settings under the
 * key role_menu_map as JSON: { "admin": ["dashboard","clients",...], ... }.
 * When the map is empty/absent every role sees every menu item (default).
 */
class MenuPermissions
{
    /**
     * All menu keys used by the sidebar (kept in sync with sidebar.blade.php).
     */
    public const MENUS = [
        'dashboard', 'clients', 'projects', 'tasks', 'leads', 'finance',
        'proposals', 'reports', 'kb', 'files', 'time', 'automation', 'team', 'settings',
    ];

    public static function map(): array
    {
        return Cache::remember('role_menu_map', 300, function () {
            $raw = PlatformSetting::get('role_menu_map');

            if (! $raw) {
                return [];
            }

            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        });
    }

    /**
     * Is the given role allowed to see the given menu key?
     */
    public static function can(string $role, string $menuKey): bool
    {
        $map = self::map();

        // No mapping configured -> everything allowed.
        if (empty($map)) {
            return true;
        }

        // Role not listed -> allow (explicit deny only).
        if (! isset($map[$role])) {
            return true;
        }

        return in_array($menuKey, $map[$role], true);
    }

    public static function save(array $map): void
    {
        PlatformSetting::set('role_menu_map', json_encode($map));

        Cache::forget('role_menu_map');
    }
}
