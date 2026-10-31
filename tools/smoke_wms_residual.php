<?php

/**
 * Ad-hoc residual smoke: aging export + master import + cycle audit + GR/GI ops audit.
 * Not a CI suite. Run: php tools/smoke_wms_residual.php
 */

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\AgingReportController;
use App\Http\Controllers\CycleCountController;
use App\Http\Controllers\GoodsIssueController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PutawayController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\WarehouseController;
use App\Imports\SuppliersImport;
use App\Imports\WarehousesImport;
use App\Models\AuditLog;
use App\Models\GoodsIssue;
use App\Models\GoodsReceipt;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PutawayTask;
use App\Models\Stockopname;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;

$pass = 0;
$fail = 0;
$lines = [];

function check(string $name, bool $ok, string $detail = ''): void
{
    global $pass, $fail, $lines;
    if ($ok) {
        $pass++;
        $lines[] = "PASS  {$name}" . ($detail !== '' ? " — {$detail}" : '');
    } else {
        $fail++;
        $lines[] = "FAIL  {$name}" . ($detail !== '' ? " — {$detail}" : '');
    }
}

$admin = User::where('email', 'admin.gudang@fazarth.my.id')->first();
$super = User::where('email', 'superadmin@fazarth.my.id')->first();
check('seed users present', $admin && $super);

auth()->login($admin);

// --- Aging export formats ---
$aging = app(AgingReportController::class);
foreach (['csv', 'xlsx', 'pdf'] as $fmt) {
    $req = Request::create('/reports/aging', 'GET', ['format' => $fmt]);
    $req->setUserResolver(fn () => $admin);
    try {
        $resp = $aging->index($req);
        $code = method_exists($resp, 'getStatusCode') ? $resp->getStatusCode() : 200;
        check("aging export {$fmt}", $code === 200, "status={$code}");
    } catch (Throwable $e) {
        check("aging export {$fmt}", false, $e->getMessage());
    }
}

// --- Supplier import (controller + Excel class) ---
$path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'wms-sup-import.csv';
file_put_contents($path, "code,name,address,phone,email,contact_person,is_active\nSUP-RES-001,Residual Supplier,,,supres@test.local,A,1\n");
$import = new SuppliersImport(false);
try {
    Excel::import($import, $path);
    check('supplier import class', $import->created >= 1 || Supplier::where('code', 'SUP-RES-001')->exists(), "created={$import->created}");
} catch (Throwable $e) {
    check('supplier import class', false, $e->getMessage());
}

// --- Warehouse import ---
$pathW = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'wms-wh-import.csv';
file_put_contents($pathW, "code,name,description,location,is_active\nWH-RES-001,Residual WH,import smoke,Area R,1\n");
$importW = new WarehousesImport(false);
try {
    Excel::import($importW, $pathW);
    check('warehouse import class', $importW->created >= 1 || Warehouse::where('code', 'WH-RES-001')->exists(), "created={$importW->created}");
} catch (Throwable $e) {
    check('warehouse import class', false, $e->getMessage());
}

// Controller import wrappers (UploadedFile)
try {
    $supFile = new UploadedFile($path, 'wms-sup-import.csv', 'text/csv', null, true);
    $req = Request::create('/suppliers/import', 'POST', ['update_existing' => 1], [], ['file' => $supFile]);
    $req->setUserResolver(fn () => $admin);
    $resp = app(SupplierController::class)->import($req);
    check('supplier import controller', method_exists($resp, 'getTargetUrl'), get_class($resp));
} catch (Throwable $e) {
    check('supplier import controller', false, $e->getMessage());
}

try {
    $whFile = new UploadedFile($pathW, 'wms-wh-import.csv', 'text/csv', null, true);
    $req = Request::create('/warehouses/import', 'POST', ['update_existing' => 1], [], ['file' => $whFile]);
    $req->setUserResolver(fn () => $admin);
    $resp = app(WarehouseController::class)->import($req);
    check('warehouse import controller', method_exists($resp, 'getTargetUrl'), get_class($resp));
} catch (Throwable $e) {
    check('warehouse import controller', false, $e->getMessage());
}

// --- Cycle count create + submit audit ---
$wh = Warehouse::where('is_active', true)->first();
$bin = Location::where('type', 'bin')->where('is_active', true)->where('warehouse_id', $wh?->id)->first()
    ?: Location::where('type', 'bin')->where('is_active', true)->first();
if ($wh) {
    try {
        $req = Request::create('/cycle-counts', 'POST', [
            'warehouse_id' => $wh->id,
            'location_id' => $bin?->id,
            'stockopname_date' => now()->toDateString(),
            'notes' => 'residual smoke cycle',
        ]);
        $req->setUserResolver(fn () => $admin);
        $resp = app(CycleCountController::class)->store($req);
        $cc = Stockopname::where('mode', 'cycle')->latest('id')->first();
        check('cycle create', (bool) $cc, $cc?->stockopname_number ?? 'none');
        check('cycle create audit', AuditLog::where('action', 'ops.cycle.create')->where('model_id', $cc?->id)->exists());

        if ($cc && $cc->status === 'draft') {
            $req2 = Request::create('/cycle-counts/'.$cc->id.'/submit', 'POST');
            $req2->setUserResolver(fn () => $admin);
            // use submit method
            app(CycleCountController::class)->submit($cc);
            $cc->refresh();
            check('cycle submit', $cc->status === 'pending_approval', $cc->status);
            check('cycle submit audit', AuditLog::where('action', 'ops.cycle.submit')->where('model_id', $cc->id)->exists());
        } else {
            check('cycle submit', false, 'no draft cycle');
            check('cycle submit audit', false, 'skipped');
        }
    } catch (Throwable $e) {
        check('cycle create', false, $e->getMessage());
        check('cycle create audit', false, 'skipped');
        check('cycle submit', false, 'skipped');
        check('cycle submit audit', false, 'skipped');
    }
} else {
    check('cycle create', false, 'no warehouse');
    check('cycle create audit', false, 'skipped');
    check('cycle submit', false, 'skipped');
    check('cycle submit audit', false, 'skipped');
}

// --- GR create + verify (ops audit) ---
$supplier = Supplier::where('is_active', true)->first() ?: Supplier::first();
$product = Product::where('is_active', true)->first();
$whOps = Warehouse::where('is_active', true)->first();
if ($supplier && $product && $whOps) {
    try {
        $req = Request::create('/goods-receipts', 'POST', [
            'supplier_id' => $supplier->id,
            'receipt_date' => now()->toDateString(),
            'notes' => 'residual smoke GR',
            'items' => [[
                'product_id' => $product->id,
                'warehouse_id' => $whOps->id,
                'quantity' => 2,
                'batch_number' => 'RES-BATCH',
                'lot_number' => null,
                'expired_date' => now()->addMonths(6)->toDateString(),
            ]],
        ]);
        $req->setUserResolver(fn () => $admin);
        $resp = app(GoodsReceiptController::class)->store($req);
        $gr = GoodsReceipt::latest('id')->first();
        check('gr create', $gr && $gr->status === 'pending', $gr?->receipt_number ?? 'none');
        check('gr create audit', AuditLog::where('action', 'ops.gr.create')->where('model_id', $gr?->id)->exists());

        if ($gr) {
            $gr->load('items');
            $itemsPayload = [];
            foreach ($gr->items as $item) {
                $itemsPayload[] = [
                    'id' => $item->id,
                    'qty_pass' => (int) $item->quantity,
                    'qty_fail' => 0,
                ];
            }
            $reqV = Request::create('/goods-receipts/'.$gr->id.'/verify', 'POST', [
                'items' => $itemsPayload,
                'skip_qc' => 1,
            ]);
            $reqV->setUserResolver(fn () => $admin);
            app(GoodsReceiptController::class)->verify($reqV, $gr);
            $gr->refresh();
            check('gr verify', $gr->status === 'verified', $gr->status);
            check('gr verify audit', AuditLog::where('action', 'ops.gr.verify')->where('model_id', $gr->id)->exists());

            // putaway complete if task exists
            $task = PutawayTask::where('goods_receipt_id', $gr->id)->where('status', 'pending')->first();
            $binForPut = Location::where('warehouse_id', $whOps->id)->where('type', 'bin')->where('is_active', true)->first()
                ?: Location::where('type', 'bin')->where('is_active', true)->first();
            if ($task && $binForPut) {
                $reqP = Request::create('/putaways/'.$task->id.'/complete', 'POST', [
                    'location_id' => $binForPut->id,
                ]);
                $reqP->setUserResolver(fn () => $admin);
                app(PutawayController::class)->complete($reqP, $task);
                $task->refresh();
                check('putaway complete', in_array($task->status, ['completed', 'done', 'finished'], true) || $task->status !== 'pending', $task->status);
                check('putaway audit', AuditLog::where('action', 'ops.putaway.complete')->where('model_id', $task->id)->exists());
            } else {
                check('putaway complete', true, 'no pending task/bin — skipped OK');
                check('putaway audit', true, 'skipped');
            }
        } else {
            check('gr verify', false, 'no gr');
            check('gr verify audit', false, 'skipped');
            check('putaway complete', false, 'skipped');
            check('putaway audit', false, 'skipped');
        }
    } catch (Throwable $e) {
        check('gr create', false, $e->getMessage());
        check('gr create audit', false, 'skipped');
        check('gr verify', false, 'skipped');
        check('gr verify audit', false, 'skipped');
        check('putaway complete', false, 'skipped');
        check('putaway audit', false, 'skipped');
    }
} else {
    check('gr create', false, 'missing supplier/product/wh');
    check('gr create audit', false, 'skipped');
    check('gr verify', false, 'skipped');
    check('gr verify audit', false, 'skipped');
    check('putaway complete', false, 'skipped');
    check('putaway audit', false, 'skipped');
}

// --- GI create if bin stock available ---
$binStock = ProductStock::where('quantity', '>', 0)->whereNotNull('location_id')->first();
if ($binStock) {
    try {
        $req = Request::create('/goods-issues', 'POST', [
            'destination' => 'Residual smoke dest',
            'issue_date' => now()->toDateString(),
            'notes' => 'residual smoke GI',
            'items' => [[
                'product_id' => $binStock->product_id,
                'warehouse_id' => $binStock->warehouse_id,
                'quantity' => 1,
                'batch_number' => $binStock->batch_number,
            ]],
        ]);
        $req->setUserResolver(fn () => $admin);
        app(GoodsIssueController::class)->store($req);
        $gi = GoodsIssue::latest('id')->first();
        check('gi create', (bool) $gi, $gi?->issue_number ?? 'none');
        check('gi create audit', AuditLog::where('action', 'ops.gi.create')->where('model_id', $gi?->id)->exists());
    } catch (Throwable $e) {
        check('gi create', false, $e->getMessage());
        check('gi create audit', false, 'skipped');
    }
} else {
    check('gi create', true, 'no bin stock — skipped OK');
    check('gi create audit', true, 'skipped');
}

// Audit log actions present
$needed = [
    'report.export',
    'master.supplier.import_create',
    'master.warehouse.import_create',
    'ops.cycle.create',
];
foreach ($needed as $action) {
    check("audit has {$action}", AuditLog::where('action', $action)->exists());
}

echo "=== WMS residual smoke ===\n";
foreach ($lines as $l) {
    echo $l . PHP_EOL;
}
echo "SUMMARY: {$pass}/" . ($pass + $fail) . " PASS\n";
exit($fail === 0 ? 0 : 1);
