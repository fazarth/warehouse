<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
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
        if (!auth()->user()->hasRole('Super Admin') || !auth()->user()->can('role.manage')) {
            abort(403);
        }

        // Hard lock: only Super Admin may edit Super Admin role permissions
        if ($role->name === 'Super Admin' && !auth()->user()->hasRole('Super Admin')) {
            abort(403);
        }

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $before = $role->permissions->pluck('name')->sort()->values()->all();
        $permissions = $validated['permissions'] ?? [];
        $role->syncPermissions($permissions);
        $after = $role->fresh()->permissions->pluck('name')->sort()->values()->all();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        AuditLog::log(
            'admin.role.permissions_update',
            Role::class,
            $role->id,
            "Permission role {$role->name} diperbarui",
            ['permissions' => $before],
            ['permissions' => $after]
        );

        return redirect()->route('admin.roles.index')
            ->with('success', "Permission untuk role {$role->name} berhasil diperbarui!");
    }
}
