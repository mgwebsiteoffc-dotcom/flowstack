<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Support\MenuPermissions;
use Illuminate\Http\Request;

class SuperAdminRoleController extends Controller
{
    /**
     * Role -> menu mapping editor.
     */
    public function index()
    {
        $roles = ['admin', 'ops_manager', 'account_manager', 'specialist'];
        $menus = MenuPermissions::MENUS;
        $map = MenuPermissions::map();

        return view('super-admin.roles', compact('roles', 'menus', 'map'));
    }

    public function save(Request $request)
    {
        $roles = ['admin', 'ops_manager', 'account_manager', 'specialist'];

        $map = [];

        foreach ($roles as $role) {
            $map[$role] = array_values(array_intersect(
                MenuPermissions::MENUS,
                array_keys($request->input('menus_'.$role, []))
            ));
        }

        MenuPermissions::save($map);

        return back()->with('success', 'Role-menu mapping saved.');
    }
}
