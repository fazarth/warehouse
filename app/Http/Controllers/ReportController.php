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
use App\Support\TabularExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $kpi = [
            'products' => Product::where('is_active', true)->count(),
            'warehouses' => Warehouse::where('is_active', true)->count(),
            'total_stock' => (int) ProductStock::sum('quantity'),
            'open_po' => PurchaseOrder::whereIn('status', ['draft', 'ordered', 'partial'])->count(),
            'pending_gr' => GoodsReceipt::where('status', 'pending')->count(),
            'open_gi' => GoodsIssue::whereIn('status', ['pending', 'picking', 'packing', 'packed'])->count(),
            'pending_so' => Stockopname::where('status', 'pending_approval')->count(),
            'gr_7d' => GoodsReceipt::where('created_at', '>=', now()->subDays(7))->count(),
            'gi_7d' => GoodsIssue::where('created_at', '>=', now()->subDays(7))->count(),
            'low_stock' => $this->lowStockRows()->count(),
            'near_expiry' => 0,
            'expired' => 0,
        ];

        $aging = app(AgingService::class)->summary();
        $kpi['near_expiry'] = (int) (($aging['0-30']['qty'] ?? 0) + ($aging['31-60']['qty'] ?? 0));
        $kpi['expired'] = (int) ($aging['expired']['qty'] ?? 0);

        return view('reports.index', compact('kpi'));
    }

    public function receipts(Request $request)
    {
        $startDate = $request->input('start_date', $request->input('date_from'));
        $endDate = $request->input('end_date', $request->input('date_to'));

        $query = GoodsReceipt::with(['supplier', 'user'])
            ->withCount('items')
            ->withSum('items', 'quantity');

        if ($startDate) {
            $query->whereDate('receipt_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('receipt_date', '<=', $endDate);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($format = $this->exportFormat($request)) {
            $rows = $query->orderBy('receipt_date', 'desc')->get();
            $mapped = $rows->map(fn ($r) => [
                $r->receipt_number,
                optional($r->receipt_date)->format('Y-m-d'),
                $r->supplier->name ?? '-',
                $r->status,
                $r->items_count,
                $r->items_sum_quantity ?? 0,
            ]);

            return $this->export(
                'report-receipts',
                'Laporan Penerimaan',
                ['No', 'Tanggal', 'Supplier', 'Status', 'Items', 'Total Qty'],
                $mapped,
                $format
            );
        }

        $receipts = $query->orderBy('receipt_date', 'desc')->paginate(30)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();

        return view('reports.receipts', compact('receipts', 'suppliers', 'startDate', 'endDate'));
    }

    public function issues(Request $request)
    {
        $startDate = $request->input('start_date', $request->input('date_from'));
        $endDate = $request->input('end_date', $request->input('date_to'));

        $query = GoodsIssue::with(['user'])->withCount('items')->withSum('items', 'quantity');

        if ($startDate) {
            $query->whereDate('issue_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('issue_date', '<=', $endDate);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($format = $this->exportFormat($request)) {
            $rows = $query->orderBy('issue_date', 'desc')->get();
            $mapped = $rows->map(fn ($i) => [
                $i->issue_number,
                optional($i->issue_date)->format('Y-m-d'),
                $i->status,
                $i->items_count,
                $i->items_sum_quantity ?? 0,
                $i->user->name ?? '-',
            ]);

            return $this->export(
                'report-issues',
                'Laporan Pengiriman',
                ['No', 'Tanggal', 'Status', 'Items', 'Total Qty', 'User'],
                $mapped,
                $format
            );
        }

        $issues = $query->orderBy('issue_date', 'desc')->paginate(30)->withQueryString();

        return view('reports.issues', compact('issues', 'startDate', 'endDate'));
    }

    public function stock(Request $request)
    {
        $query = Product::query()->withSum(['stocks as stocks_sum_quantity' => function ($q) use ($request) {
            if ($request->filled('warehouse_id')) {
                $q->where('warehouse_id', $request->warehouse_id);
            }
        }], 'quantity');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($format = $this->exportFormat($request)) {
            $rows = $query->orderBy('name')->get();
            $mapped = $rows->map(fn ($p) => [
                $p->sku,
                $p->name,
                $p->category,
                $p->unit,
                $p->stocks_sum_quantity ?? 0,
                $p->min_stock ?? 0,
            ]);

            return $this->export(
                'report-stock',
                'Laporan Stok',
                ['SKU', 'Nama', 'Kategori', 'Unit', 'Total Qty', 'Min Stock'],
                $mapped,
                $format
            );
        }

        $products = $query->orderBy('name')->paginate(50)->withQueryString();
        $categories = Product::whereNotNull('category')->distinct()->orderBy('category')->pluck('category');
        $warehouses = Warehouse::orderBy('name')->get();

        return view('reports.stock', compact('products', 'warehouses', 'categories'));
    }

    public function mutations(Request $request)
    {
        $startDate = $request->input('start_date', $request->input('date_from'));
        $endDate = $request->input('end_date', $request->input('date_to'));

        $query = StockMutation::with(['product', 'fromWarehouse', 'toWarehouse', 'user']);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('type')) {
            $query->where(function ($q) use ($request) {
                $q->where('type', $request->type)
                    ->orWhere('reference_type', $request->type);
            });
        }

        if ($format = $this->exportFormat($request)) {
            $rows = $query->orderBy('created_at', 'desc')->limit(5000)->get();
            $mapped = $rows->map(fn ($m) => [
                $m->created_at?->format('Y-m-d H:i'),
                $m->product->sku ?? '-',
                $m->quantity,
                $m->type ?? $m->reference_type,
                $m->fromWarehouse->code ?? '-',
                $m->toWarehouse->code ?? '-',
                $m->user->name ?? '-',
            ]);

            return $this->export(
                'report-mutations',
                'Laporan Mutasi Stok',
                ['Waktu', 'SKU', 'Qty', 'Tipe', 'Dari', 'Ke', 'User'],
                $mapped,
                $format
            );
        }

        $mutations = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();
        $products = Product::orderBy('name')->get();

        return view('reports.mutations', compact('mutations', 'products', 'startDate', 'endDate'));
    }

    public function lowStock(Request $request)
    {
        $rows = $this->lowStockRows();

        if ($format = $this->exportFormat($request)) {
            $mapped = $rows->map(fn ($r) => [
                $r['sku'], $r['name'], $r['min_stock'], $r['on_hand'], $r['shortage'],
            ]);

            return $this->export(
                'report-low-stock',
                'Laporan Low Stock',
                ['SKU', 'Nama', 'Min', 'On Hand', 'Shortage'],
                $mapped,
                $format
            );
        }

        return view('reports.low-stock', compact('rows'));
    }

    protected function lowStockRows()
    {
        $onHand = ProductStock::select('product_id', DB::raw('SUM(quantity) as qty'))
            ->groupBy('product_id')
            ->pluck('qty', 'product_id');

        return Product::where('is_active', true)
            ->where('min_stock', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(function (Product $p) use ($onHand) {
                $qty = (int) ($onHand[$p->id] ?? 0);
                $min = (int) $p->min_stock;

                return [
                    'product_id' => $p->id,
                    'sku' => $p->sku,
                    'name' => $p->name,
                    'unit' => $p->unit,
                    'min_stock' => $min,
                    'on_hand' => $qty,
                    'shortage' => max(0, $min - $qty),
                    'is_low' => $qty <= $min,
                ];
            })
            ->filter(fn ($r) => $r['is_low'])
            ->values();
    }

    protected function exportFormat(Request $request): ?string
    {
        if ($request->filled('format')) {
            $format = strtolower((string) $request->input('format'));

            return in_array($format, ['csv', 'xlsx', 'pdf'], true) ? $format : null;
        }

        // Backward compatible: ?export=1 → csv
        if ($request->boolean('export')) {
            return 'csv';
        }

        return null;
    }

    protected function export(string $basename, string $title, array $headers, $rows, string $format)
    {
        $count = is_countable($rows) ? count($rows) : 0;
        TabularExport::logExport($basename, $format, $count);

        return TabularExport::download($basename, $headers, $rows, $format, $title);
    }
}
