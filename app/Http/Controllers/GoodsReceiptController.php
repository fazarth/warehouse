<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMutation;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\PutawayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodsReceiptController extends Controller
{
    public function __construct(private PutawayService $putaway)
    {
    }

    public function index(Request $request)
    {
        $query = GoodsReceipt::with(['supplier', 'user', 'verifier', 'purchaseOrder']);

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

    public function create(Request $request)
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $purchaseOrders = PurchaseOrder::with('items.product')
            ->whereIn('status', ['ordered', 'partial'])
            ->orderByDesc('id')
            ->get();
        $selectedPo = $request->integer('purchase_order_id')
            ? $purchaseOrders->firstWhere('id', $request->integer('purchase_order_id'))
            : null;

        return view('goods-receipts.create', compact('suppliers', 'products', 'warehouses', 'purchaseOrders', 'selectedPo'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
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
            'items.*.purchase_order_item_id' => 'nullable|exists:purchase_order_items,id',
        ]);

        try {
            $receipt = DB::transaction(function () use ($validated) {
                if (!empty($validated['purchase_order_id'])) {
                    $po = PurchaseOrder::findOrFail($validated['purchase_order_id']);
                    if ((int) $po->supplier_id !== (int) $validated['supplier_id']) {
                        throw new \RuntimeException('Supplier GR harus sama dengan PO.');
                    }
                    if (in_array($po->status, ['cancelled', 'draft'], true)) {
                        throw new \RuntimeException('PO tidak siap diterima.');
                    }
                }

                $receipt = GoodsReceipt::create([
                    'receipt_number' => GoodsReceipt::generateReceiptNumber(),
                    'supplier_id' => $validated['supplier_id'],
                    'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                    'receipt_date' => $validated['receipt_date'],
                    'document_number' => $validated['document_number'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'status' => 'pending',
                    'qc_status' => 'pending',
                    'user_id' => auth()->id(),
                ]);

                foreach ($validated['items'] as $item) {
                    GoodsReceiptItem::create([
                        'goods_receipt_id' => $receipt->id,
                        'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                        'product_id' => $item['product_id'],
                        'warehouse_id' => $item['warehouse_id'],
                        'quantity' => $item['quantity'],
                        'batch_number' => $item['batch_number'] ?? null,
                        'lot_number' => $item['lot_number'] ?? null,
                        'expired_date' => $item['expired_date'] ?? null,
                    ]);
                }

                return $receipt;
                            });

                            AuditLog::log(
                                'ops.gr.create',
                                GoodsReceipt::class,
                                $receipt->id,
                                'GR dibuat: '.$receipt->receipt_number,
                                null,
                                [
                                    'receipt_number' => $receipt->receipt_number,
                                    'supplier_id' => $receipt->supplier_id,
                                    'purchase_order_id' => $receipt->purchase_order_id,
                                    'items' => count($validated['items']),
                                ]
                            );

                            return redirect()->route('goods-receipts.show', $receipt)
                                ->with('success', 'Penerimaan barang berhasil dibuat dengan nomor '.$receipt->receipt_number);
                        } catch (\Throwable $e) {
                            return back()->withInput()->with('error', 'Gagal menyimpan: '.$e->getMessage());
                        }
                    }

    public function show(GoodsReceipt $goodsReceipt)
    {
        $goodsReceipt->load([
            'supplier', 'user', 'verifier', 'qcUser', 'purchaseOrder',
            'items.product', 'items.warehouse', 'items.purchaseOrderItem',
            'putawayTasks.suggestedLocation', 'putawayTasks.location',
        ]);

        return view('goods-receipts.show', compact('goodsReceipt'));
    }

    /**
     * QC + stock-in staging + spawn putaway tasks.
     * Replaces old verify that put stock without bin.
     */
    public function verify(Request $request, GoodsReceipt $goodsReceipt)
        {
            abort_unless(auth()->user()->can('goods_receipt.verify'), 403);

            if ($goodsReceipt->status !== 'pending') {
                return back()->with('error', 'Penerimaan sudah diverifikasi.');
            }

        $goodsReceipt->load('items');

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:goods_receipt_items,id',
            'items.*.qty_pass' => 'required|integer|min:0',
            'items.*.qty_fail' => 'nullable|integer|min:0',
            'items.*.qc_notes' => 'nullable|string|max:255',
            'skip_qc' => 'nullable|boolean',
        ]);

        try {
            DB::transaction(function () use ($goodsReceipt, $validated) {
                $skip = (bool) ($validated['skip_qc'] ?? false);
                $totalPass = 0;
                $totalFail = 0;

                foreach ($validated['items'] as $row) {
                    $item = $goodsReceipt->items->firstWhere('id', (int) $row['id']);
                    if (!$item) {
                        throw new \RuntimeException('Item GR tidak valid.');
                    }

                    $pass = $skip ? (int) $item->quantity : (int) $row['qty_pass'];
                    $fail = $skip ? 0 : (int) ($row['qty_fail'] ?? max(0, (int) $item->quantity - $pass));

                    if ($pass + $fail > (int) $item->quantity) {
                        throw new \RuntimeException('Qty QC melebihi qty terima untuk item #'.$item->id);
                    }

                    $item->update([
                        'qty_pass' => $pass,
                        'qty_fail' => $fail,
                        'qc_notes' => $row['qc_notes'] ?? null,
                    ]);

                    $totalPass += $pass;
                    $totalFail += $fail;

                    // Stock-in only PASS qty to staging (location_id null)
                    if ($pass > 0) {
                        $stock = ProductStock::firstOrCreate(
                            [
                                'product_id' => $item->product_id,
                                'warehouse_id' => $item->warehouse_id,
                                'location_id' => null,
                                'batch_number' => $item->batch_number,
                                'lot_number' => $item->lot_number,
                            ],
                            [
                                'expired_date' => $item->expired_date,
                                'quantity' => 0,
                            ]
                        );
                        $stock->increment('quantity', $pass);

                        StockMutation::create([
                            'product_id' => $item->product_id,
                            'from_warehouse_id' => null,
                            'to_warehouse_id' => $item->warehouse_id,
                            'batch_number' => $item->batch_number,
                            'quantity' => $pass,
                            'type' => 'receipt',
                            'reference_type' => 'goods_receipt',
                            'reference_id' => $goodsReceipt->id,
                            'notes' => 'GR '.$goodsReceipt->receipt_number.' QC pass (staging)',
                            'user_id' => auth()->id(),
                        ]);
                    }

                    // Update PO received qty
                    if ($item->purchase_order_item_id && $pass > 0) {
                        $poi = PurchaseOrderItem::lockForUpdate()->find($item->purchase_order_item_id);
                        if ($poi) {
                            $poi->increment('quantity_received', $pass);
                        }
                    }
                }

                $qcStatus = $skip
                    ? 'skipped'
                    : ($totalFail <= 0 ? 'passed' : ($totalPass <= 0 ? 'failed' : 'partial'));

                $goodsReceipt->update([
                    'status' => 'verified',
                    'verified_by' => auth()->id(),
                    'verified_at' => now(),
                    'qc_status' => $qcStatus,
                    'qc_by' => auth()->id(),
                    'qc_at' => now(),
                ]);

                if ($goodsReceipt->purchase_order_id) {
                    PurchaseOrder::find($goodsReceipt->purchase_order_id)?->refreshStatus();
                }

                // Spawn putaway only when there is pass qty
                                if ($totalPass > 0) {
                                    $this->putaway->createTasksFromReceipt($goodsReceipt->fresh('items'));
                                }

                                AuditLog::log(
                                    'ops.gr.verify',
                                    GoodsReceipt::class,
                                    $goodsReceipt->id,
                                    'GR diverifikasi/QC: '.$goodsReceipt->receipt_number,
                                    ['status' => 'pending'],
                                    [
                                        'status' => 'verified',
                                        'qc_status' => $goodsReceipt->fresh()->qc_status,
                                        'total_pass' => $totalPass,
                                        'total_fail' => $totalFail,
                                        'skip_qc' => $skip,
                                    ]
                                );
                            });

                            return redirect()->route('goods-receipts.show', $goodsReceipt)
                                ->with('success', 'QC selesai. Stok staging masuk & putaway task dibuat.');
                        } catch (\Throwable $e) {
                            return back()->withInput()->with('error', 'Gagal verifikasi: '.$e->getMessage());
                        }
                    }
                }
