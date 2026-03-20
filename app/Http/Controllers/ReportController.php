<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\GoodsIssue;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Stockopname;
use App\Models\StockMutation;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function receipts(Request $request)
    {
        $query = GoodsReceipt::with(['supplier', 'user', 'items']);

        if ($request->filled('date_from')) {
            $query->whereDate('receipt_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('receipt_date', '<=', $request->date_to);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $receipts = $query->orderBy('receipt_date', 'desc')->paginate(30);

        $suppliers = \App\Models\Supplier::orderBy('name')->get();

        return view('reports.receipts', compact('receipts', 'suppliers'));
    }

    public function issues(Request $request)
    {
        $query = GoodsIssue::with(['user', 'items']);

        if ($request->filled('date_from')) {
            $query->whereDate('issue_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('issue_date', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $issues = $query->orderBy('issue_date', 'desc')->paginate(30);

        return view('reports.issues', compact('issues'));
    }

    public function stock(Request $request)
    {
        $query = ProductStock::with(['product', 'warehouse']);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $stocks = $query->orderBy('product_id')->get();

        // Group by product
        $stockByProduct = $stocks->groupBy('product_id')->map(function ($items) {
            $product = $items->first()->product;
            return [
                'product' => $product,
                'total_qty' => $items->sum('quantity'),
                'warehouses' => $items->map(fn($s) => [
                    'warehouse' => $s->warehouse->name,
                    'qty' => $s->quantity,
                    'batch' => $s->batch_number,
                    'expired' => $s->expired_date?->format('d/m/Y'),
                ]),
            ];
        });

        $warehouses = Warehouse::orderBy('name')->get();

        return view('reports.stock', compact('stockByProduct', 'warehouses'));
    }

    public function mutations(Request $request)
    {
        $query = StockMutation::with(['product', 'fromWarehouse', 'toWarehouse', 'user']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('type')) {
            $query->where('reference_type', $request->type);
        }

        $mutations = $query->orderBy('created_at', 'desc')->paginate(50);

        $products = Product::orderBy('name')->get();

        return view('reports.mutations', compact('mutations', 'products'));
    }
}
