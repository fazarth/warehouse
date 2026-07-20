<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = PurchaseOrder::with(['supplier', 'user'])
            ->withSum('items as qty_ordered', 'quantity_ordered')
            ->withSum('items as qty_received', 'quantity_received')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->where(function ($qq) use ($s) {
                    $qq->where('po_number', 'like', "%{$s}%")
                        ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$s}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('purchase-orders.index', compact('orders'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('purchase-orders.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ]);

        try {
            $po = DB::transaction(function () use ($validated) {
                $po = PurchaseOrder::create([
                    'po_number' => PurchaseOrder::generateNumber(),
                    'supplier_id' => $validated['supplier_id'],
                    'order_date' => $validated['order_date'],
                    'expected_date' => $validated['expected_date'] ?? null,
                    'status' => 'ordered',
                    'notes' => $validated['notes'] ?? null,
                    'user_id' => auth()->id(),
                ]);

                foreach ($validated['items'] as $item) {
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'product_id' => $item['product_id'],
                        'quantity_ordered' => $item['quantity_ordered'],
                        'quantity_received' => 0,
                        'unit_price' => $item['unit_price'] ?? null,
                    ]);
                }

                return $po;
            });

            return redirect()->route('purchase-orders.show', $po)
                ->with('success', 'PO '.$po->po_number.' dibuat.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Gagal simpan PO: '.$e->getMessage());
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'user', 'items.product', 'goodsReceipts']);

        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if (in_array($purchaseOrder->status, ['received', 'cancelled'], true)) {
            return back()->with('error', 'PO tidak bisa dibatalkan.');
        }

        $purchaseOrder->update(['status' => 'cancelled']);

        return back()->with('success', 'PO dibatalkan.');
    }
}
