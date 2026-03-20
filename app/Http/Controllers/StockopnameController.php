<?php

namespace App\Http\Controllers;

use App\Models\Stockopname;
use App\Models\StockopnameItem;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Warehouse;
use App\Models\StockMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockopnameController extends Controller
{
    public function index(Request $request)
    {
        $query = Stockopname::with(['warehouse', 'user', 'approver']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $stockopnames = $query->latest()->paginate(15);
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('stockopnames.index', compact('stockopnames', 'warehouses'));
    }

    public function create()
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('stockopnames.create', compact('warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'stockopname_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.physical_qty' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $stockopname = Stockopname::create([
                'stockopname_number' => Stockopname::generateNumber(),
                'warehouse_id' => $validated['warehouse_id'],
                'stockopname_date' => $validated['stockopname_date'],
                'notes' => $validated['notes'],
                'status' => 'draft',
                'user_id' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                // Get current system quantity
                $systemQty = ProductStock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $validated['warehouse_id'])
                    ->sum('quantity');

                StockopnameItem::create([
                    'stockopname_id' => $stockopname->id,
                    'product_id' => $item['product_id'],
                    'system_qty' => $systemQty,
                    'physical_qty' => $item['physical_qty'],
                    'difference' => $item['physical_qty'] - $systemQty,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('stockopnames.show', $stockopname)
                ->with('success', 'Stock opname berhasil dibuat dengan nomor ' . $stockopname->stockopname_number);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function show(Stockopname $stockopname)
    {
        $stockopname->load(['warehouse', 'user', 'approver', 'items.product']);
        return view('stockopnames.show', compact('stockopname'));
    }

    public function submit(Stockopname $stockopname)
    {
        if ($stockopname->status !== 'draft') {
            return back()->with('error', 'Stock opname sudah disubmit.');
        }

        $stockopname->update(['status' => 'pending_approval']);

        return back()->with('success', 'Stock opname telah disubmit untuk approval.');
    }

    public function approve(Stockopname $stockopname)
    {
        if ($stockopname->status !== 'pending_approval') {
            return back()->with('error', 'Status stock opname tidak valid.');
        }

        DB::beginTransaction();
        try {
            // Adjust stock based on differences
            foreach ($stockopname->items as $item) {
                if ($item->difference != 0) {
                    // Find the main stock record (without batch) or create one
                    $stock = ProductStock::firstOrCreate(
                        [
                            'product_id' => $item->product_id,
                            'warehouse_id' => $stockopname->warehouse_id,
                            'batch_number' => null,
                            'lot_number' => null,
                        ],
                        ['quantity' => 0]
                    );

                    // Adjust the total stock to match physical count
                    // First, get total current stock for this product in this warehouse
                    $currentTotal = ProductStock::where('product_id', $item->product_id)
                        ->where('warehouse_id', $stockopname->warehouse_id)
                        ->sum('quantity');

                    $adjustment = $item->physical_qty - $currentTotal;

                    if ($adjustment != 0) {
                        $stock->increment('quantity', $adjustment);

                        // Record mutation
                        StockMutation::create([
                            'product_id' => $item->product_id,
                            'from_warehouse_id' => $adjustment < 0 ? $stockopname->warehouse_id : null,
                            'to_warehouse_id' => $adjustment > 0 ? $stockopname->warehouse_id : null,
                            'batch_number' => null,
                            'quantity' => $adjustment,
                            'reference_type' => 'stockopname',
                            'reference_id' => $stockopname->id,
                            'notes' => 'Penyesuaian stock opname ' . $stockopname->stockopname_number,
                            'user_id' => auth()->id(),
                        ]);
                    }
                }
            }

            $stockopname->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Stock opname berhasil disetujui dan stok telah disesuaikan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal approve: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, Stockopname $stockopname)
    {
        if ($stockopname->status !== 'pending_approval') {
            return back()->with('error', 'Status stock opname tidak valid.');
        }

        $stockopname->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'notes' => $stockopname->notes . "\n\nDitolak: " . ($request->rejection_reason ?? 'Tidak ada alasan'),
        ]);

        return back()->with('success', 'Stock opname ditolak.');
    }

    // AJAX: Get products with stock in warehouse
    public function getWarehouseProducts(Warehouse $warehouse)
    {
        $products = Product::where('is_active', true)
            ->get()
            ->map(function ($product) use ($warehouse) {
                $systemQty = ProductStock::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouse->id)
                    ->sum('quantity');

                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'unit' => $product->unit,
                    'system_qty' => $systemQty,
                ];
            });

        return response()->json($products);
    }
}
