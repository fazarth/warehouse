<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display list of roles
     */
    public function index()
    {
        $roles = Role::withCount('permissions', 'users')
            ->orderBy('name')
            ->get();
        
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show role permissions
     */
    public function permissions(Role $role)
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function($p) {
            return explode('.', $p->name)[0];
        });
        
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        
        return view('admin.roles.permissions', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update role permissions
     */
    public function updatePermissions(Request $request, Role $role)
    {
        // Prevent modifying Super Admin permissions by non-super admins
        if ($role->name === 'Super Admin' && !auth()->user()->hasRole('Super Admin')) {
            abort(403);
        }
        
        $permissions = $request->input('permissions', []);
        
        $role->syncPermissions($permissions);
        
        return redirect()->route('admin.roles.index')
            ->with('success', "Permission untuk role {$role->name} berhasil diperbarui!");
    }
}
