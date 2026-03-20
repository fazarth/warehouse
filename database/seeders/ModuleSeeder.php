<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            // ===== CORE MODULES (Cannot be disabled) =====
            [
                'code' => 'warehouse',
                'name' => 'Master Gudang',
                'description' => 'Kelola lokasi dan rak gudang',
                'icon' => '🏭',
                'is_enabled' => true,
                'is_core' => true,
                'package' => 'basic',
                'sort_order' => 1,
            ],
            [
                'code' => 'product',
                'name' => 'Master Produk',
                'description' => 'Kelola data produk dan SKU',
                'icon' => '📦',
                'is_enabled' => true,
                'is_core' => true,
                'package' => 'basic',
                'sort_order' => 2,
            ],
            [
                'code' => 'supplier',
                'name' => 'Master Supplier',
                'description' => 'Kelola data supplier',
                'icon' => '🚚',
                'is_enabled' => true,
                'is_core' => true,
                'package' => 'basic',
                'sort_order' => 3,
            ],
            [
                'code' => 'goods_receipt',
                'name' => 'Penerimaan Barang',
                'description' => 'Kelola penerimaan barang dari supplier',
                'icon' => '📥',
                'is_enabled' => true,
                'is_core' => true,
                'package' => 'basic',
                'sort_order' => 4,
            ],
            [
                'code' => 'goods_issue',
                'name' => 'Pengiriman Barang',
                'description' => 'Kelola pengiriman barang keluar',
                'icon' => '📤',
                'is_enabled' => true,
                'is_core' => true,
                'package' => 'basic',
                'sort_order' => 5,
            ],
            [
                'code' => 'inventory',
                'name' => 'Stok & Inventori',
                'description' => 'Kelola stok dan kartu stok',
                'icon' => '📊',
                'is_enabled' => true,
                'is_core' => true,
                'package' => 'basic',
                'sort_order' => 6,
            ],
            [
                'code' => 'report',
                'name' => 'Laporan',
                'description' => 'Laporan penerimaan, pengiriman, stok',
                'icon' => '📄',
                'is_enabled' => true,
                'is_core' => true,
                'package' => 'basic',
                'sort_order' => 7,
            ],
            [
                'code' => 'setting',
                'name' => 'Pengaturan',
                'description' => 'Konfigurasi sistem',
                'icon' => '⚙️',
                'is_enabled' => true,
                'is_core' => true,
                'package' => 'basic',
                'sort_order' => 99,
            ],

            // ===== PRO MODULES =====
            [
                'code' => 'stockopname',
                'name' => 'Stock Opname',
                'description' => 'Penghitungan fisik dan penyesuaian stok',
                'icon' => '📋',
                'is_enabled' => true,
                'is_core' => false,
                'package' => 'pro',
                'sort_order' => 8,
            ],
            [
                'code' => 'mutation',
                'name' => 'Mutasi Stok',
                'description' => 'Transfer stok antar gudang',
                'icon' => '🔄',
                'is_enabled' => true,
                'is_core' => false,
                'package' => 'pro',
                'sort_order' => 9,
            ],

            // ===== FULL MODULES =====
            [
                'code' => 'batch_tracking',
                'name' => 'Batch/Lot Tracking',
                'description' => 'Track batch number dan lot number',
                'icon' => '🏷️',
                'is_enabled' => true,
                'is_core' => false,
                'package' => 'full',
                'sort_order' => 10,
            ],
            [
                'code' => 'expiry_management',
                'name' => 'Expiry Management',
                'description' => 'Kelola produk dengan tanggal kadaluarsa',
                'icon' => '📅',
                'is_enabled' => true,
                'is_core' => false,
                'package' => 'full',
                'sort_order' => 11,
            ],
            [
                'code' => 'api',
                'name' => 'REST API',
                'description' => 'Akses API untuk integrasi',
                'icon' => '🔌',
                'is_enabled' => false,
                'is_core' => false,
                'package' => 'full',
                'sort_order' => 12,
            ],
        ];

        foreach ($modules as $module) {
            Module::updateOrCreate(
                ['code' => $module['code']],
                $module
            );
        }
        
        // Remove old Koperasi modules
        Module::whereIn('code', ['member', 'saving', 'loan', 'finance', 'shu', 'notification', 'branch', 'member_portal'])->delete();
    }
}
