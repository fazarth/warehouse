<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\GoodsIssue;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Stockopname;
use App\Models\StockMutation;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'warehouses' => Warehouse::where('is_active', true)->count(),
            'products' => Product::where('is_active', true)->count(),
            'suppliers' => Supplier::where('is_active', true)->count(),
            'totalStock' => ProductStock::sum('quantity'),
            'todayReceipts' => GoodsReceipt::whereDate('created_at', today())->count(),
            'todayReceiptQty' => GoodsReceipt::whereDate('created_at', today())
                ->where('status', 'verified')
                ->with('items')
                ->get()
                ->sum(fn($r) => $r->items->sum('quantity')),
            'todayIssues' => GoodsIssue::whereDate('created_at', today())->count(),
            'todayIssueQty' => GoodsIssue::whereDate('created_at', today())
                ->whereIn('status', ['shipped', 'delivered'])
                ->with('items')
                ->get()
                ->sum(fn($i) => $i->items->sum('quantity')),
            'pendingReceipts' => GoodsReceipt::where('status', 'pending')->count(),
            'pendingStockopnames' => Stockopname::where('status', 'pending_approval')->count(),
        ];

        // Recent Goods Receipts
        $recentReceipts = GoodsReceipt::with(['supplier', 'user'])
            ->latest()
            ->take(5)
            ->get();

        // Recent Goods Issues
        $recentIssues = GoodsIssue::with('user')
            ->latest()
            ->take(5)
            ->get();

        // Recent Mutations
        $recentMutations = StockMutation::with(['product', 'fromWarehouse', 'toWarehouse', 'user'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'recentReceipts', 'recentIssues', 'recentMutations'));
    }
}
