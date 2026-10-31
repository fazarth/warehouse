<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Hard-coded RBAC matrix for Enterprise WMS.
 *
 * | Role          | Intent                                              |
 * |---------------|-----------------------------------------------------|
 * | Super Admin   | Full system + role/module mapping                   |
 * | Admin Gudang  | Full warehouse ops + users + app settings           |
 * | Supervisor    | Oversight: view + verify/approve/ship + reports     |
 * | Operator      | Floor only: scan, putaway, GR/GI create, SO count   |
 *                 | NO master CRUD, NO PO, NO report, NO admin          |
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'warehouse.view', 'warehouse.create', 'warehouse.edit', 'warehouse.delete',
            'location.view', 'location.create', 'location.edit', 'location.delete',
            'supplier.view', 'supplier.create', 'supplier.edit', 'supplier.delete',
            'product.view', 'product.create', 'product.edit', 'product.delete',
            'purchase_order.view', 'purchase_order.create', 'purchase_order.edit',
            'goods_receipt.view', 'goods_receipt.create', 'goods_receipt.verify',
            'putaway.view', 'putaway.complete',
            'goods_issue.view', 'goods_issue.create', 'goods_issue.ship',
            'stockopname.view', 'stockopname.create', 'stockopname.approve',
            'return.view', 'return.create', 'return.receive',
            'cycle_count.view', 'cycle_count.create',
            'inventory.view', 'inventory.mutation',
            'report.view',
            'setting.view', 'setting.edit',
            'user.view', 'user.create', 'user.edit', 'user.delete',
            'role.manage',
            'module.manage',
            'audit.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Super Admin: everything (includes audit.view)
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::where('guard_name', 'web')->get());

        // Admin Gudang: full ops + users + app settings; NO role/module mapping / audit log
        $adminGudang = Role::firstOrCreate(['name' => 'Admin Gudang', 'guard_name' => 'web']);
        $adminGudang->syncPermissions([
            'warehouse.view', 'warehouse.create', 'warehouse.edit', 'warehouse.delete',
            'location.view', 'location.create', 'location.edit', 'location.delete',
            'supplier.view', 'supplier.create', 'supplier.edit', 'supplier.delete',
            'product.view', 'product.create', 'product.edit', 'product.delete',
            'purchase_order.view', 'purchase_order.create', 'purchase_order.edit',
            'goods_receipt.view', 'goods_receipt.create', 'goods_receipt.verify',
            'putaway.view', 'putaway.complete',
            'goods_issue.view', 'goods_issue.create', 'goods_issue.ship',
            'stockopname.view', 'stockopname.create', 'stockopname.approve',
            'return.view', 'return.create', 'return.receive',
            'cycle_count.view', 'cycle_count.create',
            'inventory.view', 'inventory.mutation',
            'report.view',
            'setting.view', 'setting.edit',
            'user.view', 'user.create', 'user.edit', 'user.delete',
        ]);

        // Supervisor: view masters + verify/ship/approve + reports; NO master CRUD, NO PO create, NO users
        $supervisor = Role::firstOrCreate(['name' => 'Supervisor', 'guard_name' => 'web']);
        $supervisor->syncPermissions([
            'warehouse.view',
            'location.view',
            'supplier.view',
            'product.view',
            'purchase_order.view',
            'goods_receipt.view', 'goods_receipt.verify',
            'putaway.view', 'putaway.complete',
            'goods_issue.view', 'goods_issue.ship',
            'stockopname.view', 'stockopname.approve',
            'return.view', 'return.receive',
            'cycle_count.view',
            'inventory.view', 'inventory.mutation',
            'report.view',
            'setting.view',
        ]);

        // Operator: floor only — NO master pages, NO PO, NO verify/ship/approve, NO report, NO admin
        // product/location master routes stay closed; scan does direct DB lookup (auth only).
        $operator = Role::firstOrCreate(['name' => 'Operator', 'guard_name' => 'web']);
        $operator->syncPermissions([
            'goods_receipt.view', 'goods_receipt.create',
            'putaway.view', 'putaway.complete',
            'goods_issue.view', 'goods_issue.create',
            'stockopname.view', 'stockopname.create',
            'return.view', 'return.create',
            'cycle_count.view', 'cycle_count.create',
            'inventory.view',
            'setting.view',
        ]);

        $this->seedUsers();

        $this->command?->info('RBAC matrix reseeds OK (tight Operator/Supervisor).');
        $this->command?->table(
            ['Role', 'Email', 'Password', 'Scope'],
            [
                ['Super Admin', 'superadmin@fazarth.my.id', 'superadmin123!', 'All + Roles + Modules'],
                ['Admin Gudang', 'admin.gudang@fazarth.my.id', 'password', 'Ops full + Users + Settings'],
                ['Supervisor', 'supervisor@fazarth.my.id', 'password', 'View + verify/ship + reports'],
                ['Operator', 'operator@fazarth.my.id', 'password', 'Floor only (no master/PO/report)'],
            ]
        );
    }

    protected function seedUsers(): void
    {
        $map = [
            ['email' => 'superadmin@fazarth.my.id', 'name' => 'Super Admin Gudang', 'password' => 'superadmin123!', 'role' => 'Super Admin'],
            ['email' => 'admin.gudang@fazarth.my.id', 'name' => 'Admin Gudang', 'password' => 'password', 'role' => 'Admin Gudang'],
            ['email' => 'supervisor@fazarth.my.id', 'name' => 'Supervisor Gudang', 'password' => 'password', 'role' => 'Supervisor'],
            ['email' => 'operator@fazarth.my.id', 'name' => 'Operator Gudang', 'password' => 'password', 'role' => 'Operator'],
        ];

        foreach ($map as $row) {
            $user = User::firstOrCreate(
                ['email' => $row['email']],
                ['name' => $row['name'], 'password' => Hash::make($row['password'])]
            );
            $user->syncRoles([$row['role']]);
        }
    }
}
