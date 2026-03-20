<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\StockMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodsReceiptController extends Controller
{
    public function index(Request $request)
    {
        $query = GoodsReceipt::with(['supplier', 'user', 'verifier']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('receipt_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('receipt_date', '<=', $request->date_to);
        }

        $receipts = $query->latest()->paginate(15);

        return view('goods-receipts.index', compact('receipts'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('goods-receipts.create', compact('suppliers', 'products', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'receipt_date' => 'required|date',
            'document_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.warehouse_id' => 'required|exists:warehouses,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.batch_number' => 'nullable|string|max:50',
            'items.*.lot_number' => 'nullable|string|max:50',
            'items.*.expired_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $receipt = GoodsReceipt::create([
                'receipt_number' => GoodsReceipt::generateReceiptNumber(),
                'supplier_id' => $validated['supplier_id'],
                'receipt_date' => $validated['receipt_date'],
                'document_number' => $validated['document_number'],
                'notes' => $validated['notes'],
                'status' => 'pending',
                'user_id' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                GoodsReceiptItem::create([
                    'goods_receipt_id' => $receipt->id,
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $item['warehouse_id'],
                    'quantity' => $item['quantity'],
                    'batch_number' => $item['batch_number'] ?? null,
                    'lot_number' => $item['lot_number'] ?? null,
                    'expired_date' => $item['expired_date'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('goods-receipts.show', $receipt)
                ->with('success', 'Penerimaan barang berhasil dibuat dengan nomor ' . $receipt->receipt_number);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function show(GoodsReceipt $goodsReceipt)
    {
        $goodsReceipt->load(['supplier', 'user', 'verifier', 'items.product', 'items.warehouse']);
        return view('goods-receipts.show', compact('goodsReceipt'));
    }

    public function verify(GoodsReceipt $goodsReceipt)
    {
        if ($goodsReceipt->status !== 'pending') {
            return back()->with('error', 'Penerimaan sudah diverifikasi.');
        }

        DB::beginTransaction();
        try {
            // Update stock for each item
            foreach ($goodsReceipt->items as $item) {
                // Find or create product stock record
                $stock = ProductStock::firstOrCreate(
                    [
                        'product_id' => $item->product_id,
                        'warehouse_id' => $item->warehouse_id,
                        'batch_number' => $item->batch_number,
                        'lot_number' => $item->lot_number,
                    ],
                    [
                        'expired_date' => $item->expired_date,
                        'quantity' => 0,
                    ]
                );

                $qtyBefore = $stock->quantity;
                $stock->increment('quantity', $item->quantity);

                // Record stock mutation
                StockMutation::create([
                    'product_id' => $item->product_id,
                    'from_warehouse_id' => null,
                    'to_warehouse_id' => $item->warehouse_id,
                    'batch_number' => $item->batch_number,
                    'quantity' => $item->quantity,
                    'reference_type' => 'goods_receipt',
                    'reference_id' => $goodsReceipt->id,
                    'notes' => 'Penerimaan barang ' . $goodsReceipt->receipt_number,
                    'user_id' => auth()->id(),
                ]);
            }

            // Update receipt status
            $goodsReceipt->update([
                'status' => 'verified',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Penerimaan barang berhasil diverifikasi dan stok telah diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal verifikasi: ' . $e->getMessage());
        }
    }
}
