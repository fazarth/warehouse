<?php

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'admin.gudang@fazarth.my.id')->firstOrFail();
auth()->login($user);

$sku = 'OPS-AUDIT-PROD-1';
App\Models\Product::where('sku', $sku)->delete();

$req = Illuminate\Http\Request::create('/products', 'POST', [
    'sku' => $sku,
    'name' => 'Ops Audit Product',
    'unit' => 'PCS',
    'category' => 'Smoke',
    'min_stock' => 1,
    'is_active' => 1,
]);
$req->setUserResolver(fn () => $user);
$c = app(App\Http\Controllers\ProductController::class);
$c->store($req);

$p = App\Models\Product::where('sku', $sku)->firstOrFail();
$req2 = Illuminate\Http\Request::create('/products/' . $p->id, 'PUT', [
    'sku' => $sku,
    'name' => 'Ops Audit Product Updated',
    'unit' => 'PCS',
    'category' => 'Smoke',
    'min_stock' => 2,
    'is_active' => 1,
]);
$req2->setUserResolver(fn () => $user);
$c->update($req2, $p);

$actions = App\Models\AuditLog::query()
    ->whereIn('action', ['master.product.create', 'master.product.update', 'master.product.import', 'master.product.import_create', 'report.export', 'ops.gr.create', 'ops.gi.create'])
    ->orderByDesc('id')
    ->limit(12)
    ->get(['action', 'description']);

echo "product_audit_count=" . App\Models\AuditLog::where('action', 'like', 'master.product.%')->count() . PHP_EOL;
echo "report_export_count=" . App\Models\AuditLog::where('action', 'report.export')->count() . PHP_EOL;
foreach ($actions as $a) {
    echo $a->action . ' | ' . $a->description . PHP_EOL;
}
