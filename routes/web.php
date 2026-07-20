<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\GoodsIssueController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PutawayController;
use App\Http\Controllers\StockopnameController;
use App\Http\Controllers\StockReturnController;
use App\Http\Controllers\CycleCountController;
use App\Http\Controllers\AgingReportController;
use App\Http\Controllers\MobileScanController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Warehouse Management Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // QR Scan console (all authenticated warehouse users)
    Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
    Route::post('/scan', [ScanController::class, 'lookup'])->name('scan.lookup');

    // Master Data: Bin / Zone / Rack hierarchy — static paths before {location}
    Route::middleware(['permission:location.view'])->group(function () {
        Route::get('locations', [LocationController::class, 'index'])->name('locations.index');
    });
    Route::middleware(['permission:location.create'])->group(function () {
        Route::get('locations/create', [LocationController::class, 'create'])->name('locations.create');
        Route::post('locations', [LocationController::class, 'store'])->name('locations.store');
    });
    Route::middleware(['permission:location.view'])->group(function () {
        Route::get('locations/{location}', [LocationController::class, 'show'])->name('locations.show');
        Route::get('locations/{location}/label', [LocationController::class, 'label'])->name('locations.label');
    });
    Route::middleware(['permission:location.edit'])->group(function () {
        Route::get('locations/{location}/edit', [LocationController::class, 'edit'])->name('locations.edit');
        Route::put('locations/{location}', [LocationController::class, 'update'])->name('locations.update');
    });
    Route::middleware(['permission:location.delete'])->group(function () {
        Route::delete('locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');
    });

    // Master Data: Warehouses (Locations/Racks)
    Route::middleware(['permission:warehouse.view'])->group(function () {
        Route::get('warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
    });
    Route::middleware(['permission:warehouse.create'])->group(function () {
        Route::get('warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
        Route::post('warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
    });
    Route::middleware(['permission:warehouse.edit'])->group(function () {
        Route::get('warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
        Route::put('warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
    });
    Route::middleware(['permission:warehouse.delete'])->group(function () {
        Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');
    });

    // Master Data: Suppliers
    Route::middleware(['permission:supplier.view'])->group(function () {
        Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    });
    Route::middleware(['permission:supplier.create'])->group(function () {
        Route::get('suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    });
    Route::middleware(['permission:supplier.edit'])->group(function () {
        Route::get('suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    });
    Route::middleware(['permission:supplier.delete'])->group(function () {
        Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });

    // Master Data: Products
    Route::middleware(['permission:product.create'])->group(function () {
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
    });
    Route::middleware(['permission:product.view'])->group(function () {
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
    });
    Route::middleware(['permission:product.edit'])->group(function () {
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
    });
    Route::middleware(['permission:product.delete'])->group(function () {
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    // Purchase Orders — static create before {purchaseOrder}
    Route::middleware(['permission:purchase_order.view'])->group(function () {
        Route::get('purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    });
    Route::middleware(['permission:purchase_order.create'])->group(function () {
        Route::get('purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
        Route::post('purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
    });
    Route::middleware(['permission:purchase_order.view'])->group(function () {
        Route::get('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    });
    Route::middleware(['permission:purchase_order.edit'])->group(function () {
        Route::post('purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
    });

    // Goods Receipt (Penerimaan Barang)
    Route::middleware(['permission:goods_receipt.create'])->group(function () {
        Route::get('goods-receipts/create', [GoodsReceiptController::class, 'create'])->name('goods-receipts.create');
        Route::post('goods-receipts', [GoodsReceiptController::class, 'store'])->name('goods-receipts.store');
    });
    Route::middleware(['permission:goods_receipt.view'])->group(function () {
        Route::get('goods-receipts', [GoodsReceiptController::class, 'index'])->name('goods-receipts.index');
        Route::get('goods-receipts/{goodsReceipt}', [GoodsReceiptController::class, 'show'])->name('goods-receipts.show');
    });
    Route::middleware(['permission:goods_receipt.verify'])->group(function () {
        Route::post('goods-receipts/{goodsReceipt}/verify', [GoodsReceiptController::class, 'verify'])->name('goods-receipts.verify');
    });

    // Put Away
    Route::middleware(['permission:putaway.view'])->group(function () {
        Route::get('putaways', [PutawayController::class, 'index'])->name('putaways.index');
        Route::get('putaways/{putaway}', [PutawayController::class, 'show'])->name('putaways.show');
    });
    Route::middleware(['permission:putaway.complete'])->group(function () {
        Route::post('putaways/{putaway}/complete', [PutawayController::class, 'complete'])->name('putaways.complete');
    });

    // Goods Issue (Pengiriman Barang)
    Route::middleware(['permission:goods_issue.create'])->group(function () {
        Route::get('goods-issues/create', [GoodsIssueController::class, 'create'])->name('goods-issues.create');
        Route::post('goods-issues', [GoodsIssueController::class, 'store'])->name('goods-issues.store');
    });
    Route::middleware(['permission:goods_issue.view'])->group(function () {
        Route::get('goods-issues', [GoodsIssueController::class, 'index'])->name('goods-issues.index');
        Route::get('goods-issues/{goodsIssue}', [GoodsIssueController::class, 'show'])->name('goods-issues.show');
    });
    Route::middleware(['permission:goods_issue.ship'])->group(function () {
        Route::post('goods-issues/{goodsIssue}/allocate', [GoodsIssueController::class, 'allocate'])->name('goods-issues.allocate');
        Route::post('goods-issues/{goodsIssue}/pick', [GoodsIssueController::class, 'pick'])->name('goods-issues.pick');
        Route::post('goods-issues/{goodsIssue}/pack', [GoodsIssueController::class, 'pack'])->name('goods-issues.pack');
        Route::post('goods-issues/{goodsIssue}/ship', [GoodsIssueController::class, 'ship'])->name('goods-issues.ship');
        Route::post('goods-issues/{goodsIssue}/deliver', [GoodsIssueController::class, 'deliver'])->name('goods-issues.deliver');
    });

    // Stockopname
    Route::middleware(['permission:stockopname.create'])->group(function () {
        Route::get('stockopnames/create', [StockopnameController::class, 'create'])->name('stockopnames.create');
        Route::post('stockopnames', [StockopnameController::class, 'store'])->name('stockopnames.store');
        Route::post('stockopnames/{stockopname}/submit', [StockopnameController::class, 'submit'])->name('stockopnames.submit');
    });
    Route::middleware(['permission:stockopname.view'])->group(function () {
        Route::get('stockopnames', [StockopnameController::class, 'index'])->name('stockopnames.index');
        Route::get('stockopnames/{stockopname}', [StockopnameController::class, 'show'])->name('stockopnames.show');
    });
    Route::middleware(['permission:stockopname.approve'])->group(function () {
        Route::post('stockopnames/{stockopname}/approve', [StockopnameController::class, 'approve'])->name('stockopnames.approve');
        Route::post('stockopnames/{stockopname}/reject', [StockopnameController::class, 'reject'])->name('stockopnames.reject');
    });
    // AJAX for stockopname
    Route::get('api/warehouses/{warehouse}/products', [StockopnameController::class, 'getWarehouseProducts'])->name('api.warehouse-products');

    // Inventory (Stock Overview & Mutations)
    Route::middleware(['permission:inventory.view'])->group(function () {
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('inventory/stock-card/{product}', [InventoryController::class, 'stockCard'])->name('inventory.stock-card');
    });
    Route::middleware(['permission:inventory.mutation'])->group(function () {
        Route::get('inventory/mutation', [InventoryController::class, 'mutation'])->name('inventory.mutation');
        Route::post('inventory/mutation', [InventoryController::class, 'mutationStore'])->name('inventory.mutation.store');
    });

    // W4 Returns — static create before {return}
    Route::middleware(['permission:return.create'])->group(function () {
        Route::get('returns/create', [StockReturnController::class, 'create'])->name('returns.create');
        Route::post('returns', [StockReturnController::class, 'store'])->name('returns.store');
    });
    Route::middleware(['permission:return.view'])->group(function () {
        Route::get('returns', [StockReturnController::class, 'index'])->name('returns.index');
        Route::get('returns/{return}', [StockReturnController::class, 'show'])->name('returns.show');
    });
    Route::middleware(['permission:return.receive'])->group(function () {
        Route::post('returns/{return}/receive', [StockReturnController::class, 'receive'])->name('returns.receive');
    });

    // W4 Cycle Count — static create before {cycle_count}
    Route::middleware(['permission:cycle_count.create'])->group(function () {
        Route::get('cycle-counts/create', [CycleCountController::class, 'create'])->name('cycle-counts.create');
        Route::post('cycle-counts', [CycleCountController::class, 'store'])->name('cycle-counts.store');
        Route::put('cycle-counts/{cycle_count}', [CycleCountController::class, 'updateCounts'])->name('cycle-counts.update');
        Route::post('cycle-counts/{cycle_count}/submit', [CycleCountController::class, 'submit'])->name('cycle-counts.submit');
    });
    Route::middleware(['permission:cycle_count.view'])->group(function () {
        Route::get('cycle-counts', [CycleCountController::class, 'index'])->name('cycle-counts.index');
        Route::get('cycle-counts/{cycle_count}', [CycleCountController::class, 'show'])->name('cycle-counts.show');
    });

    // W5 Mobile scanner (auth only — Zebra friendly)
    Route::get('m/scan', [MobileScanController::class, 'index'])->name('mobile.scan');
    Route::post('m/scan', [MobileScanController::class, 'lookup'])->name('mobile.scan.lookup');

    // Reports
    Route::middleware(['permission:report.view'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/receipts', [ReportController::class, 'receipts'])->name('receipts');
        Route::get('/issues', [ReportController::class, 'issues'])->name('issues');
        Route::get('/stock', [ReportController::class, 'stock'])->name('stock');
        Route::get('/mutations', [ReportController::class, 'mutations'])->name('mutations');
        Route::get('/aging', [AgingReportController::class, 'index'])->name('aging');
        Route::get('/low-stock', [ReportController::class, 'lowStock'])->name('low-stock');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\SettingsController::class, 'index'])->name('index');
        Route::get('/account', [App\Http\Controllers\SettingsController::class, 'account'])->name('account');
        Route::put('/account', [App\Http\Controllers\SettingsController::class, 'updateAccount'])->name('account.update');
        Route::get('/password', [App\Http\Controllers\SettingsController::class, 'password'])->name('password');
        Route::put('/password', [App\Http\Controllers\SettingsController::class, 'updatePassword'])->name('password.update');
        
        Route::middleware(['permission:setting.edit'])->group(function () {
            Route::get('/app', [App\Http\Controllers\SettingsController::class, 'app'])->name('app');
            Route::put('/app', [App\Http\Controllers\SettingsController::class, 'updateApp'])->name('app.update');
        });
    });

    // Admin: Super Admin system tools (modules + role mapping)
    Route::prefix('admin')->name('admin.')->middleware(['role:Super Admin', 'permission:module.manage'])->group(function () {
        Route::get('modules', [App\Http\Controllers\Admin\ModuleController::class, 'index'])->name('modules.index');
        Route::patch('modules/{code}/toggle', [App\Http\Controllers\Admin\ModuleController::class, 'toggle'])->name('modules.toggle');
    });

    Route::prefix('admin')->name('admin.')->middleware(['role:Super Admin', 'permission:role.manage'])->group(function () {
        Route::get('roles', [App\Http\Controllers\Admin\RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/{role}/permissions', [App\Http\Controllers\Admin\RoleController::class, 'permissions'])->name('roles.permissions');
        Route::put('roles/{role}/permissions', [App\Http\Controllers\Admin\RoleController::class, 'updatePermissions'])->name('roles.permissions.update');
    });

    // Admin: user management (Super Admin + Admin Gudang via permission)
    Route::prefix('admin')->name('admin.')->middleware(['permission:user.view'])->group(function () {
        Route::get('users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create')->middleware('permission:user.create');
        Route::post('users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store')->middleware('permission:user.create');
        Route::get('users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit')->middleware('permission:user.edit');
        Route::put('users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update')->middleware('permission:user.edit');
        Route::delete('users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:user.delete');
    });
});
