<?php

namespace App\Http\Controllers;

use App\Models\GoodsIssue;
use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\StockMutation;
use App\Models\Stockopname;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\AgingService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'warehouses' => Warehouse::where('is_active', true)->count(),
            'products' => Product::where('is_active', true)->count(),
            'suppliers' => Supplier::where('is_active', true)->count(),
            'totalStock' => (int) ProductStock::sum('quantity'),
            'todayReceipts' => GoodsReceipt::whereDate('created_at', today())->count(),
            'todayReceiptQty' => GoodsReceipt::whereDate('created_at', today())
                ->where('status', 'verified')
                ->with('items')
                ->get()
                ->sum(fn ($r) => $r->items->sum('quantity')),
            'todayIssues' => GoodsIssue::whereDate('created_at', today())->count(),
            'todayIssueQty' => GoodsIssue::whereDate('created_at', today())
                ->whereIn('status', ['shipped', 'delivered'])
                ->with('items')
                ->get()
                ->sum(fn ($i) => $i->items->sum('quantity')),
            'pendingReceipts' => GoodsReceipt::where('status', 'pending')->count(),
            'pendingStockopnames' => Stockopname::where('status', 'pending_approval')->count(),
            'openPo' => PurchaseOrder::whereIn('status', ['draft', 'ordered', 'partial'])->count(),
            'openGi' => GoodsIssue::whereIn('status', ['pending', 'picking', 'packing', 'packed'])->count(),
            'lowStock' => 0,
            'nearExpiry' => 0,
            'expired' => 0,
        ];

        // Low stock KPI (min_stock > 0 and on-hand <= min)
        $onHand = ProductStock::select('product_id', DB::raw('SUM(quantity) as qty'))
            ->groupBy('product_id')
            ->pluck('qty', 'product_id');
        $stats['lowStock'] = Product::where('is_active', true)
            ->where('min_stock', '>', 0)
            ->get()
            ->filter(fn ($p) => (int) ($onHand[$p->id] ?? 0) <= (int) $p->min_stock)
            ->count();

        $aging = app(AgingService::class)->summary();
        $stats['nearExpiry'] = (int) (($aging['0-30']['qty'] ?? 0) + ($aging['31-60']['qty'] ?? 0));
        $stats['expired'] = (int) ($aging['expired']['qty'] ?? 0);

        $recentReceipts = GoodsReceipt::with(['supplier', 'user'])->latest()->take(5)->get();
        $recentIssues = GoodsIssue::with('user')->latest()->take(5)->get();
        $recentMutations = StockMutation::with(['product', 'fromWarehouse', 'toWarehouse', 'user'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'recentReceipts', 'recentIssues', 'recentMutations'));
    }
}
