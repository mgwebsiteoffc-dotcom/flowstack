<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Support\MenuPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

/**
 * Admin (tenant owner) user management:
 *  - change any user's password
 *  - toggle a user's active status
 *  - per-role menu visibility (what each role can see in the sidebar)
 */
class AdminUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index()
    {
        $users = User::withCount('assignedTasks')->orderBy('name')->get();
        $roles = User::ROLES;
        $menus = MenuPermissions::MENUS;
        $menuMap = MenuPermissions::map(); // role => [menu keys]
        $labels = $this->menuLabels();

        return view('settings.admin-users', compact('users', 'roles', 'menus', 'menuMap', 'labels'));
    }

    /**
     * Change a user's password (admin only).
     */
    public function changePassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', 'Password updated for '.$user->name.'.');
    }

    /**
     * Activate / deactivate a user.
     */
    public function toggleActive(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->name.' is now '.($user->is_active ? 'active' : 'inactive').'.');
    }

    /**
     * Save per-role menu visibility for THIS tenant.
     * Stored in settings table (tenant-level), merged with the super-admin
     * platform-wide map (tenant map wins when set).
     */
    public function saveRoleMenus(Request $request)
    {
        $validated = $request->validate([
            'menus' => ['required', 'array'],
            'menus.*' => ['array'],
        ]);

        $roles = User::ROLES;
        $map = [];

        foreach ($roles as $role) {
            $allowed = array_values(array_intersect(
                MenuPermissions::MENUS,
                array_keys($validated['menus'][$role] ?? [])
            ));
            $map[$role] = $allowed;
        }

        Setting::set('tenant_role_menu_map', json_encode($map));
        \Illuminate\Support\Facades\Cache::forget('tenant_role_menu_map');

        return back()->with('success', 'Role menu visibility saved. Team members will see the updated menu immediately.');
    }

    /**
     * Role menu resolution: tenant map overrides platform map when the tenant
     * has saved one; otherwise the platform (super-admin) map applies.
     */
    public static function resolveMap(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('tenant_role_menu_map', 120, function () {
            $tenantMap = json_decode((string) Setting::get('tenant_role_menu_map', 'null'), true);
            if (is_array($tenantMap)) {
                return $tenantMap;
            }

            return MenuPermissions::map();
        });
    }

    protected function menuLabels(): array
    {
        return [
            'dashboard' => 'Dashboard',
            'clients' => 'Clients',
            'projects' => 'Projects',
            'tasks' => 'Tasks',
            'leads' => 'Leads',
            'finance' => 'Finance',
            'proposals' => 'Proposals',
            'reports' => 'Reports',
            'kb' => 'Knowledge Base',
            'files' => 'Files',
            'time' => 'Time',
            'automation' => 'Automation',
            'team' => 'Team',
            'settings' => 'Settings',
        ];
    }
}
