<?php

namespace App\Http\Controllers;

use App\Models\GoodsIssue;
use App\Models\GoodsIssueItem;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Warehouse;
use App\Models\StockMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodsIssueController extends Controller
{
    public function index(Request $request)
    {
        $query = GoodsIssue::with(['user', 'shipper']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('issue_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('issue_date', '<=', $request->date_to);
        }

        $issues = $query->latest()->paginate(15);

        return view('goods-issues.index', compact('issues'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        // Get available stocks
        $stocks = ProductStock::with(['product', 'warehouse'])
            ->where('quantity', '>', 0)
            ->get();

        return view('goods-issues.create', compact('products', 'warehouses', 'stocks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'destination' => 'required|string|max:255',
            'issue_date' => 'required|date',
            'delivery_note' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.warehouse_id' => 'required|exists:warehouses,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.batch_number' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            // Check stock availability
            foreach ($validated['items'] as $item) {
                $stock = ProductStock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $item['warehouse_id'])
                    ->where('batch_number', $item['batch_number'] ?? null)
                    ->first();

                if (!$stock || $stock->quantity < $item['quantity']) {
                    $product = Product::find($item['product_id']);
                    throw new \Exception("Stok {$product->name} tidak mencukupi.");
                }
            }

            $issue = GoodsIssue::create([
                'issue_number' => GoodsIssue::generateIssueNumber(),
                'destination' => $validated['destination'],
                'issue_date' => $validated['issue_date'],
                'delivery_note' => $validated['delivery_note'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
                'user_id' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                GoodsIssueItem::create([
                    'goods_issue_id' => $issue->id,
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $item['warehouse_id'],
                    'quantity' => $item['quantity'],
                    'batch_number' => $item['batch_number'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('goods-issues.show', $issue)
                ->with('success', 'Pengiriman barang berhasil dibuat dengan nomor ' . $issue->issue_number);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function show(GoodsIssue $goodsIssue)
    {
        $goodsIssue->load(['user', 'shipper', 'items.product', 'items.warehouse']);
        return view('goods-issues.show', compact('goodsIssue'));
    }

    public function ship(GoodsIssue $goodsIssue)
    {
        if ($goodsIssue->status !== 'pending') {
            return back()->with('error', 'Status pengiriman tidak valid.');
        }

        DB::beginTransaction();
        try {
            // Deduct stock for each item
            foreach ($goodsIssue->items as $item) {
                $stock = ProductStock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $item->warehouse_id)
                    ->where('batch_number', $item->batch_number)
                    ->first();

                if (!$stock || $stock->quantity < $item->quantity) {
                    throw new \Exception("Stok tidak mencukupi untuk pengiriman.");
                }

                $stock->decrement('quantity', $item->quantity);

                // Record stock mutation
                StockMutation::create([
                    'product_id' => $item->product_id,
                    'from_warehouse_id' => $item->warehouse_id,
                    'to_warehouse_id' => null,
                    'batch_number' => $item->batch_number,
                    'quantity' => -$item->quantity,
                    'reference_type' => 'goods_issue',
                    'reference_id' => $goodsIssue->id,
                    'notes' => 'Pengiriman barang ' . $goodsIssue->issue_number,
                    'user_id' => auth()->id(),
                ]);
            }

            $goodsIssue->update([
                'status' => 'shipped',
                'shipped_by' => auth()->id(),
                'shipped_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Barang telah dikirim dan stok telah dikurangi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengirim: ' . $e->getMessage());
        }
    }

    public function deliver(GoodsIssue $goodsIssue)
    {
        if ($goodsIssue->status !== 'shipped') {
            return back()->with('error', 'Status pengiriman tidak valid.');
        }

        $goodsIssue->update([
            'status' => 'delivered',
        ]);

        return back()->with('success', 'Pengiriman telah dikonfirmasi diterima.');
    }
}
