<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\GoodsIssueController;
use App\Http\Controllers\StockopnameController;
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

    // Reports
    Route::middleware(['permission:report.view'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/receipts', [ReportController::class, 'receipts'])->name('receipts');
        Route::get('/issues', [ReportController::class, 'issues'])->name('issues');
        Route::get('/stock', [ReportController::class, 'stock'])->name('stock');
        Route::get('/mutations', [ReportController::class, 'mutations'])->name('mutations');
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

    // Admin Routes
    Route::prefix('admin')->name('admin.')->middleware(['role:Super Admin'])->group(function () {
        Route::get('modules', [App\Http\Controllers\Admin\ModuleController::class, 'index'])->name('modules.index');
        Route::patch('modules/{code}/toggle', [App\Http\Controllers\Admin\ModuleController::class, 'toggle'])->name('modules.toggle');
    });

    Route::prefix('admin')->name('admin.')->middleware(['permission:user.view'])->group(function () {
        Route::get('users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create')->middleware('permission:user.create');
        Route::post('users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store')->middleware('permission:user.create');
        Route::get('users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit')->middleware('permission:user.edit');
        Route::put('users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update')->middleware('permission:user.edit');
        Route::delete('users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:user.delete');
    });
});
