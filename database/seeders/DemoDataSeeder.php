<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\GoodsReceipt;
use App\Models\GoodsIssue;
use App\Models\Stockopname;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Warehouses
        $warehouses = [
            [
                'code' => 'WH-MAIN',
                'name' => 'Gudang Utama',
                'location' => 'Jakarta Pusat',
                'description' => 'Gudang penyimpanan utama',
            ],
            [
                'code' => 'WH-BRANCH',
                'name' => 'Gudang Cabang',
                'location' => 'Bekasi',
                'description' => 'Gudang distribusi timur',
            ],
        ];

        foreach ($warehouses as $wh) {
            Warehouse::firstOrCreate(['code' => $wh['code']], $wh);
        }

        // 2. Suppliers
        $suppliers = [
            [
                'code' => 'SUP-001',
                'name' => 'PT Food Sejahtera',
                'email' => 'sales@foodsejahtera.com',
                'phone' => '021-5550001',
                'address' => 'Jl. Industri No 1',
                'contact_person' => 'Budi Santoso',
            ],
            [
                'code' => 'SUP-002',
                'name' => 'CV Tech Jaya',
                'email' => 'tech@jaya.com',
                'phone' => '021-5550002',
                'address' => 'Glodok Makmur Blok A',
                'contact_person' => 'Susi Susanti',
            ],
        ];

        foreach ($suppliers as $sup) {
            Supplier::firstOrCreate(['code' => $sup['code']], $sup);
        }

        // 3. Products
        $products = [
            [
                'sku' => 'W-FOOD-001',
                'name' => 'Beras Premium 5kg',
                'category' => 'Sembako',
                'unit' => 'Sak',
                'description' => 'Beras kualitas super',
            ],
            [
                'sku' => 'W-ELC-001',
                'name' => 'Monitor LED 24"',
                'category' => 'Elektronik',
                'unit' => 'Unit',
                'description' => 'Monitor Full HD',
            ],
             [
                'sku' => 'W-MAT-001',
                'name' => 'Karton Box',
                'category' => 'Material',
                'unit' => 'Pcs',
                'description' => 'Karton box packaging',
            ],
        ];

        foreach ($products as $prod) {
            Product::firstOrCreate(['sku' => $prod['sku']], $prod);
        }

        // 4. Dummy Stock (via direct DB or logic if available, skipping complex logic for now)
        // Ideally we create GoodsReceipts to add stock
    }
}
