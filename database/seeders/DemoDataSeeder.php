<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\GoodsIssue;
use App\Models\GoodsIssueItem;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\QrCode;
use App\Models\StockMutation;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\QrCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        AppSetting::set('app_name', 'Warehouse Management');
        AppSetting::set('app_tagline', 'Sistem Manajemen Gudang');
        AppSetting::set('primary_color', '#14B8A6');
        AppSetting::set('company_name', 'Demo Warehouse');
        AppSetting::set('company_address', 'Jl. Industri No. 1, Jakarta');
        AppSetting::set('company_phone', '021-555-0100');

        $admin = User::where('email', 'admin.gudang@fazarth.my.id')->first()
            ?? User::where('email', 'superadmin@fazarth.my.id')->first()
            ?? User::query()->first();

        if (!$admin) {
            $this->command?->warn('No users found — run RolePermissionSeeder first.');
            return;
        }

        $whMain = Warehouse::updateOrCreate(
            ['code' => 'WH-MAIN'],
            [
                'name' => 'Gudang Utama',
                'description' => 'Gudang pusat distribusi',
                'location' => 'Jakarta Barat',
                'is_active' => true,
            ]
        );

        $whEast = Warehouse::updateOrCreate(
            ['code' => 'WH-EAST'],
            [
                'name' => 'Gudang Timur',
                'description' => 'Gudang cabang timur',
                'location' => 'Bekasi',
                'is_active' => true,
            ]
        );

        $suppliers = [
            ['code' => 'SUP-001', 'name' => 'PT Sumber Makmur', 'address' => 'Jakarta', 'phone' => '021-111', 'email' => 'sales@sumbermakmur.example', 'contact_person' => 'Budi', 'is_active' => true],
            ['code' => 'SUP-002', 'name' => 'CV Berkah Abadi', 'address' => 'Tangerang', 'phone' => '021-222', 'email' => 'order@berkah.example', 'contact_person' => 'Siti', 'is_active' => true],
            ['code' => 'SUP-003', 'name' => 'UD Mitra Jaya', 'address' => 'Depok', 'phone' => '021-333', 'email' => 'mitra@jaya.example', 'contact_person' => 'Andi', 'is_active' => true],
        ];

        foreach ($suppliers as $row) {
            Supplier::updateOrCreate(['code' => $row['code']], $row);
        }

        $products = [
            ['sku' => 'SKU-RICE-25', 'barcode' => '8991001000001', 'name' => 'Beras Premium 25kg', 'description' => 'Beras medium grain', 'unit' => 'sak', 'category' => 'Sembako', 'is_active' => true],
            ['sku' => 'SKU-OIL-2L', 'barcode' => '8991001000002', 'name' => 'Minyak Goreng 2L', 'description' => 'Minyak sawit kemasan', 'unit' => 'botol', 'category' => 'Sembako', 'is_active' => true],
            ['sku' => 'SKU-SUGAR-1', 'barcode' => '8991001000003', 'name' => 'Gula Pasir 1kg', 'description' => 'Gula putih', 'unit' => 'kg', 'category' => 'Sembako', 'is_active' => true],
            ['sku' => 'SKU-WATER-600', 'barcode' => '8991001000004', 'name' => 'Air Mineral 600ml', 'description' => 'Dus 24 botol', 'unit' => 'dus', 'category' => 'Minuman', 'is_active' => true],
            ['sku' => 'SKU-SOAP-1', 'barcode' => '8991001000005', 'name' => 'Sabun Cuci 1L', 'description' => 'Detergent cair', 'unit' => 'botol', 'category' => 'Kebersihan', 'is_active' => true],
        ];

        $productModels = [];
        foreach ($products as $row) {
            $productModels[$row['sku']] = Product::updateOrCreate(['sku' => $row['sku']], $row);
        }

        // ===== W1: Location hierarchy + QR registry =====
        $qr = app(QrCodeService::class);

        $zoneA = Location::updateOrCreate(
            ['warehouse_id' => $whMain->id, 'code' => 'A'],
            ['parent_id' => null, 'name' => 'Zona A — Dry', 'type' => Location::TYPE_ZONE, 'capacity' => null, 'is_active' => true]
        );
        $rackA01 = Location::updateOrCreate(
            ['warehouse_id' => $whMain->id, 'code' => 'A-01'],
            ['parent_id' => $zoneA->id, 'name' => 'Rak A-01', 'type' => Location::TYPE_RACK, 'capacity' => null, 'is_active' => true]
        );
        $binA0101 = Location::updateOrCreate(
            ['warehouse_id' => $whMain->id, 'code' => 'A-01-01'],
            ['parent_id' => $rackA01->id, 'name' => 'Bin A-01-01', 'type' => Location::TYPE_BIN, 'capacity' => 500, 'is_active' => true]
        );
        $binA0102 = Location::updateOrCreate(
            ['warehouse_id' => $whMain->id, 'code' => 'A-01-02'],
            ['parent_id' => $rackA01->id, 'name' => 'Bin A-01-02', 'type' => Location::TYPE_BIN, 'capacity' => 500, 'is_active' => true]
        );
        $binA0103 = Location::updateOrCreate(
            ['warehouse_id' => $whMain->id, 'code' => 'A-01-03'],
            ['parent_id' => $rackA01->id, 'name' => 'Bin A-01-03', 'type' => Location::TYPE_BIN, 'capacity' => 300, 'is_active' => true]
        );

        $zoneB = Location::updateOrCreate(
            ['warehouse_id' => $whMain->id, 'code' => 'B'],
            ['parent_id' => null, 'name' => 'Zona B — Fast Moving', 'type' => Location::TYPE_ZONE, 'capacity' => null, 'is_active' => true]
        );
        $rackB01 = Location::updateOrCreate(
            ['warehouse_id' => $whMain->id, 'code' => 'B-01'],
            ['parent_id' => $zoneB->id, 'name' => 'Rak B-01', 'type' => Location::TYPE_RACK, 'capacity' => null, 'is_active' => true]
        );
        $binB0101 = Location::updateOrCreate(
            ['warehouse_id' => $whMain->id, 'code' => 'B-01-01'],
            ['parent_id' => $rackB01->id, 'name' => 'Bin B-01-01', 'type' => Location::TYPE_BIN, 'capacity' => 200, 'is_active' => true]
        );

        $eastZone = Location::updateOrCreate(
            ['warehouse_id' => $whEast->id, 'code' => 'E'],
            ['parent_id' => null, 'name' => 'Zona E', 'type' => Location::TYPE_ZONE, 'capacity' => null, 'is_active' => true]
        );
        $eastBin = Location::updateOrCreate(
            ['warehouse_id' => $whEast->id, 'code' => 'E-01-01'],
            ['parent_id' => $eastZone->id, 'name' => 'Bin E-01-01', 'type' => Location::TYPE_BIN, 'capacity' => 400, 'is_active' => true]
        );

        foreach ([$zoneA, $rackA01, $binA0101, $binA0102, $binA0103, $zoneB, $rackB01, $binB0101, $eastZone, $eastBin] as $loc) {
            $qr->ensureFor($loc);
        }
        foreach ([$whMain, $whEast] as $wh) {
            $qr->ensureFor($wh);
        }
        foreach (Supplier::all() as $supplier) {
            $qr->ensureFor($supplier);
        }
        foreach ($productModels as $product) {
            $qr->ensureFor($product);
        }

        // Attach existing warehouse-level stock rows to demo bins (idempotent)
        $binMap = [
            'B-RICE-01' => $binA0101->id,
            'B-OIL-01' => $binA0102->id,
            'B-SUG-01' => $binA0103->id,
            'B-WTR-01' => $binB0101->id,
            'B-SOAP-01' => $binB0101->id,
            'B-WTR-EAST' => $eastBin->id,
        ];
        foreach ($binMap as $batch => $locationId) {
            ProductStock::where('batch_number', $batch)
                ->whereNull('location_id')
                ->update(['location_id' => $locationId]);
        }

        // Avoid duplicate demo GR/GI on re-seed
        if (GoodsReceipt::where('receipt_number', 'like', 'GR-DEMO-%')->exists()) {
            $this->command?->info('Demo GR/GI already present — locations/QR refreshed, skipping stock demo transactions.');
            $this->command?->info(sprintf(
                'Warehouses=%d Locations=%d Products=%d QR=%d',
                Warehouse::count(),
                Location::count(),
                Product::count(),
                QrCode::count()
            ));
            return;
        }

        DB::transaction(function () use ($admin, $whMain, $whEast, $productModels, $binA0101, $binA0102, $binA0103, $binB0101, $eastBin) {
            $supplier = Supplier::where('code', 'SUP-001')->first();

            $gr = GoodsReceipt::create([
                'receipt_number' => 'GR-DEMO-0001',
                'supplier_id' => $supplier->id,
                'receipt_date' => now()->subDays(3)->toDateString(),
                'document_number' => 'SJ-DEMO-001',
                'notes' => 'Seed demo goods receipt',
                'status' => 'verified',
                'user_id' => $admin->id,
                'verified_by' => $admin->id,
                'verified_at' => now()->subDays(3),
            ]);

            $grLines = [
                ['sku' => 'SKU-RICE-25', 'qty' => 100, 'batch' => 'B-RICE-01', 'bin' => $binA0101->id],
                ['sku' => 'SKU-OIL-2L', 'qty' => 200, 'batch' => 'B-OIL-01', 'bin' => $binA0102->id],
                ['sku' => 'SKU-SUGAR-1', 'qty' => 150, 'batch' => 'B-SUG-01', 'bin' => $binA0103->id],
                ['sku' => 'SKU-WATER-600', 'qty' => 80, 'batch' => 'B-WTR-01', 'bin' => $binB0101->id],
                ['sku' => 'SKU-SOAP-1', 'qty' => 60, 'batch' => 'B-SOAP-01', 'bin' => $binB0101->id],
            ];

            foreach ($grLines as $line) {
                $product = $productModels[$line['sku']];
                GoodsReceiptItem::create([
                    'goods_receipt_id' => $gr->id,
                    'product_id' => $product->id,
                    'warehouse_id' => $whMain->id,
                    'batch_number' => $line['batch'],
                    'lot_number' => null,
                    'expired_date' => now()->addYear()->toDateString(),
                    'quantity' => $line['qty'],
                ]);

                $stock = ProductStock::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'warehouse_id' => $whMain->id,
                        'location_id' => $line['bin'],
                        'batch_number' => $line['batch'],
                        'lot_number' => null,
                    ],
                    [
                        'expired_date' => now()->addYear()->toDateString(),
                        'quantity' => 0,
                    ]
                );
                $stock->increment('quantity', $line['qty']);

                StockMutation::create([
                    'product_id' => $product->id,
                    'from_warehouse_id' => null,
                    'to_warehouse_id' => $whMain->id,
                    'batch_number' => $line['batch'],
                    'quantity' => $line['qty'],
                    'type' => 'receipt',
                    'reference_type' => 'goods_receipt',
                    'reference_id' => $gr->id,
                    'notes' => 'Demo GR seed → bin '.$line['bin'],
                    'user_id' => $admin->id,
                ]);
            }

            $gi = GoodsIssue::create([
                'issue_number' => 'GI-DEMO-0001',
                'destination' => 'Toko Cabang A',
                'issue_date' => now()->subDay()->toDateString(),
                'delivery_note' => 'DO-DEMO-001',
                'notes' => 'Seed demo goods issue',
                'status' => 'shipped',
                'user_id' => $admin->id,
                'shipped_by' => $admin->id,
                'shipped_at' => now()->subDay(),
            ]);

            $giLines = [
                ['sku' => 'SKU-RICE-25', 'qty' => 20, 'batch' => 'B-RICE-01', 'bin' => $binA0101->id],
                ['sku' => 'SKU-OIL-2L', 'qty' => 40, 'batch' => 'B-OIL-01', 'bin' => $binA0102->id],
            ];

            foreach ($giLines as $line) {
                $product = $productModels[$line['sku']];
                GoodsIssueItem::create([
                    'goods_issue_id' => $gi->id,
                    'product_id' => $product->id,
                    'warehouse_id' => $whMain->id,
                    'batch_number' => $line['batch'],
                    'quantity' => $line['qty'],
                ]);

                $stock = ProductStock::where([
                    'product_id' => $product->id,
                    'warehouse_id' => $whMain->id,
                    'location_id' => $line['bin'],
                    'batch_number' => $line['batch'],
                ])->first();

                if ($stock) {
                    $stock->decrement('quantity', $line['qty']);
                }

                StockMutation::create([
                    'product_id' => $product->id,
                    'from_warehouse_id' => $whMain->id,
                    'to_warehouse_id' => null,
                    'batch_number' => $line['batch'],
                    'quantity' => $line['qty'],
                    'type' => 'issue',
                    'reference_type' => 'goods_issue',
                    'reference_id' => $gi->id,
                    'notes' => 'Demo GI seed',
                    'user_id' => $admin->id,
                ]);
            }

            $water = $productModels['SKU-WATER-600'];
            ProductStock::updateOrCreate(
                [
                    'product_id' => $water->id,
                    'warehouse_id' => $whEast->id,
                    'location_id' => $eastBin->id,
                    'batch_number' => 'B-WTR-EAST',
                    'lot_number' => null,
                ],
                [
                    'expired_date' => now()->addMonths(10)->toDateString(),
                    'quantity' => 25,
                ]
            );
        });

        $this->command?->info('Demo warehouse data seeded (incl. locations + QR).');
        $this->command?->info(sprintf(
            'Warehouses=%d Locations=%d Suppliers=%d Products=%d StockQty=%d QR=%d GR=%d GI=%d',
            Warehouse::count(),
            Location::count(),
            Supplier::count(),
            Product::count(),
            (int) ProductStock::sum('quantity'),
            QrCode::count(),
            GoodsReceipt::count(),
            GoodsIssue::count()
        ));
    }
}
