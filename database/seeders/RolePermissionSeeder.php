<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ===== CREATE PERMISSIONS (match routes exactly) =====
        $permissions = [
            // Warehouse (Lokasi Gudang)
            'warehouse.view',
            'warehouse.create',
            'warehouse.edit',
            'warehouse.delete',
            
            // Supplier
            'supplier.view',
            'supplier.create',
            'supplier.edit',
            'supplier.delete',
            
            // Product
            'product.view',
            'product.create',
            'product.edit',
            'product.delete',
            
            // Goods Receipt (Penerimaan Barang)
            'goods_receipt.view',
            'goods_receipt.create',
            'goods_receipt.verify',
            
            // Goods Issue (Pengiriman Barang)
            'goods_issue.view',
            'goods_issue.create',
            'goods_issue.ship',
            
            // Stockopname
            'stockopname.view',
            'stockopname.create',
            'stockopname.approve',
            
            // Inventory
            'inventory.view',
            'inventory.mutation',
            
            // Reports
            'report.view',
            
            // Settings
            'setting.view',
            'setting.edit',
            
            // User Management
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',
            
            // Module Management
            'module.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ===== CREATE ROLES =====
        
        // 1. Super Admin - ALL permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->syncPermissions(Permission::all());

        // 2. Admin Gudang - Full warehouse operations
        $adminGudang = Role::firstOrCreate(['name' => 'Admin Gudang']);
        $adminGudang->syncPermissions([
            // All warehouse permissions
            'warehouse.view', 'warehouse.create', 'warehouse.edit', 'warehouse.delete',
            'supplier.view', 'supplier.create', 'supplier.edit', 'supplier.delete',
            'product.view', 'product.create', 'product.edit', 'product.delete',
            'goods_receipt.view', 'goods_receipt.create', 'goods_receipt.verify',
            'goods_issue.view', 'goods_issue.create', 'goods_issue.ship',
            'stockopname.view', 'stockopname.create', 'stockopname.approve',
            'inventory.view', 'inventory.mutation',
            'report.view',
            'setting.view', 'setting.edit',
            'user.view', 'user.create', 'user.edit', 'user.delete',
        ]);

        // 3. Supervisor - Approval and oversight
        $supervisor = Role::firstOrCreate(['name' => 'Supervisor']);
        $supervisor->syncPermissions([
            'warehouse.view',
            'supplier.view',
            'product.view',
            'goods_receipt.view', 'goods_receipt.verify',
            'goods_issue.view', 'goods_issue.ship',
            'stockopname.view', 'stockopname.approve',
            'inventory.view', 'inventory.mutation',
            'report.view',
            'setting.view',
        ]);

        // 4. Operator - Daily operations (create only, no verify/approve)
        $operator = Role::firstOrCreate(['name' => 'Operator']);
        $operator->syncPermissions([
            'warehouse.view',
            'supplier.view',
            'product.view',
            'goods_receipt.view', 'goods_receipt.create',
            'goods_issue.view', 'goods_issue.create',
            'stockopname.view', 'stockopname.create',
            'inventory.view', 'inventory.mutation',
            'report.view',
            'setting.view',
        ]);

        // ===== CREATE DEFAULT USERS =====
        
        // Super Admin
        $superAdminUser = User::firstOrCreate(
            ['email' => 'superadmin@fazarth.my.id'],
            [
                'name' => 'Super Admin Gudang',
                'password' => Hash::make('superadmin123!'),
            ]
        );
        $superAdminUser->syncRoles(['Super Admin']);

        // Admin Gudang
        $adminUser = User::firstOrCreate(
            ['email' => 'admin.gudang@fazarth.my.id'],
            [
                'name' => 'Admin Gudang',
                'password' => Hash::make('password'),
            ]
        );
        $adminUser->syncRoles(['Admin Gudang']);

        // Supervisor
        $supervisorUser = User::firstOrCreate(
            ['email' => 'supervisor@fazarth.my.id'],
            [
                'name' => 'Supervisor Gudang',
                'password' => Hash::make('password'),
            ]
        );
        $supervisorUser->syncRoles(['Supervisor']);

        // Operator
        $operatorUser = User::firstOrCreate(
            ['email' => 'operator@fazarth.my.id'],
            [
                'name' => 'Operator Gudang',
                'password' => Hash::make('password'),
            ]
        );
        $operatorUser->syncRoles(['Operator']);

        $this->command->info('✅ Warehouse roles and permissions seeded successfully!');
        $this->command->newLine();
        $this->command->info('📋 Default Login Credentials:');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Super Admin', 'superadmin@fazarth.my.id', 'superadmin123!'],
                ['Admin Gudang', 'admin.gudang@fazarth.my.id', 'password'],
                ['Supervisor', 'supervisor@fazarth.my.id', 'password'],
                ['Operator', 'operator@fazarth.my.id', 'password'],
            ]
        );
    }
}
