<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Warehouse;
use App\Models\StockMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductStock::with(['product', 'warehouse'])
            ->where('quantity', '>', 0);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $stocks = $query->orderBy('product_id')->paginate(20);
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        // Summary stats
        $summary = [
            'totalItems' => ProductStock::distinct('product_id')->count('product_id'),
            'totalQty' => ProductStock::sum('quantity'),
            'lowStock' => 0, // Could implement min_stock later
            'expiringSoon' => ProductStock::where('quantity', '>', 0)
                ->where('expired_date', '<=', now()->addDays(30))
                ->count(),
        ];

        return view('inventory.index', compact('stocks', 'warehouses', 'products', 'summary'));
    }

    public function stockCard(Request $request, Product $product)
    {
        $warehouseId = $request->warehouse_id;

        // Get stock per warehouse
        $stockByWarehouse = ProductStock::with('warehouse')
            ->where('product_id', $product->id)
            ->where('quantity', '>', 0)
            ->get()
            ->groupBy('warehouse_id');

        // Get mutation history
        $mutations = StockMutation::with(['fromWarehouse', 'toWarehouse', 'user'])
            ->where('product_id', $product->id)
            ->when($warehouseId, function ($q) use ($warehouseId) {
                $q->where(function ($query) use ($warehouseId) {
                    $query->where('from_warehouse_id', $warehouseId)
                        ->orWhere('to_warehouse_id', $warehouseId);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        $warehouses = Warehouse::where('is_active', true)->get();

        return view('inventory.stock-card', compact('product', 'stockByWarehouse', 'mutations', 'warehouses', 'warehouseId'));
    }

    public function mutation()
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        // Available stocks for transfer
        $stocks = ProductStock::with(['product', 'warehouse'])
            ->where('quantity', '>', 0)
            ->get();

        return view('inventory.mutation', compact('warehouses', 'products', 'stocks'));
    }

    public function mutationStore(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'batch_number' => 'nullable|string|max:50',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Check source stock
            $sourceStock = ProductStock::where('product_id', $validated['product_id'])
                ->where('warehouse_id', $validated['from_warehouse_id'])
                ->where('batch_number', $validated['batch_number'] ?? null)
                ->first();

            if (!$sourceStock || $sourceStock->quantity < $validated['quantity']) {
                throw new \Exception('Stok tidak mencukupi untuk mutasi.');
            }

            // Deduct from source
            $sourceStock->decrement('quantity', $validated['quantity']);

            // Add to destination
            $destStock = ProductStock::firstOrCreate(
                [
                    'product_id' => $validated['product_id'],
                    'warehouse_id' => $validated['to_warehouse_id'],
                    'batch_number' => $validated['batch_number'] ?? null,
                    'lot_number' => $sourceStock->lot_number,
                ],
                [
                    'expired_date' => $sourceStock->expired_date,
                    'quantity' => 0,
                ]
            );
            $destStock->increment('quantity', $validated['quantity']);

            // Record mutation
            StockMutation::create([
                'product_id' => $validated['product_id'],
                'from_warehouse_id' => $validated['from_warehouse_id'],
                'to_warehouse_id' => $validated['to_warehouse_id'],
                'batch_number' => $validated['batch_number'],
                'quantity' => $validated['quantity'],
                'reference_type' => 'transfer',
                'reference_id' => null,
                'notes' => $validated['notes'] ?? 'Mutasi antar gudang',
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('inventory.index')
                ->with('success', 'Mutasi stok berhasil dilakukan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal mutasi: ' . $e->getMessage());
        }
    }
}
