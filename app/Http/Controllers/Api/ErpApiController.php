<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GoodsIssue;
use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\StockMutation;
use App\Models\Warehouse;
use App\Services\AgingService;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * W6 ERP-facing JSON API (Sanctum personal access tokens).
 */
class ErpApiController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:80',
        ]);

        $user = \App\Models\User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials.']]);
        }

        $token = $user->createToken($data['device_name'] ?? 'erp-integration')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $request->user()]);
    }

    public function products(Request $request)
    {
        $q = Product::query()->orderBy('sku');
        if ($request->filled('sku')) {
            $q->where('sku', $request->sku);
        }
        if ($request->boolean('active_only', true)) {
            $q->where('is_active', true);
        }

        return response()->json(['data' => $q->limit(500)->get(['id', 'sku', 'name', 'barcode', 'unit', 'is_active'])]);
    }

    public function stock(Request $request)
    {
        $q = ProductStock::with(['product:id,sku,name', 'warehouse:id,code,name', 'location:id,code'])
            ->where('quantity', '>', 0);

        if ($request->filled('warehouse_code')) {
            $wh = Warehouse::where('code', $request->warehouse_code)->first();
            if ($wh) {
                $q->where('warehouse_id', $wh->id);
            }
        }
        if ($request->filled('sku')) {
            $q->whereHas('product', fn ($p) => $p->where('sku', $request->sku));
        }

        $rows = $q->limit(1000)->get()->map(fn ($s) => [
            'sku' => $s->product?->sku,
            'product_name' => $s->product?->name,
            'warehouse' => $s->warehouse?->code,
            'location' => $s->location?->code,
            'batch' => $s->batch_number,
            'expired_date' => $s->expired_date?->format('Y-m-d'),
            'quantity' => (int) $s->quantity,
        ]);

        return response()->json(['data' => $rows]);
    }

    public function purchaseOrders(Request $request)
    {
        $q = PurchaseOrder::with('items.product:id,sku,name')->latest();
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        return response()->json([
            'data' => $q->limit(100)->get()->map(fn ($po) => [
                'po_number' => $po->po_number,
                'status' => $po->status,
                'order_date' => $po->order_date?->format('Y-m-d'),
                'items' => $po->items->map(fn ($i) => [
                    'sku' => $i->product?->sku,
                    'qty' => (int) $i->quantity,
                    'received' => (int) ($i->quantity_received ?? 0),
                ]),
            ]),
        ]);
    }

    public function goodsReceipts(Request $request)
    {
        $q = GoodsReceipt::with('items.product:id,sku')->latest();
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        return response()->json([
            'data' => $q->limit(100)->get()->map(fn ($gr) => [
                'receipt_number' => $gr->receipt_number,
                'status' => $gr->status,
                'receipt_date' => $gr->receipt_date?->format('Y-m-d'),
                'items' => $gr->items->map(fn ($i) => [
                    'sku' => $i->product?->sku,
                    'qty' => (int) $i->quantity,
                    'qc_status' => $i->qc_status ?? null,
                ]),
            ]),
        ]);
    }

    public function goodsIssues(Request $request)
    {
        $q = GoodsIssue::with('items.product:id,sku')->latest();
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        return response()->json([
            'data' => $q->limit(100)->get()->map(fn ($gi) => [
                'issue_number' => $gi->issue_number,
                'destination' => $gi->destination,
                'status' => $gi->status,
                'strategy' => $gi->strategy,
                'issue_date' => $gi->issue_date?->format('Y-m-d'),
                'tracking' => $gi->tracking_number,
                'items' => $gi->items->map(fn ($i) => [
                    'sku' => $i->product?->sku,
                    'qty' => (int) $i->quantity,
                ]),
            ]),
        ]);
    }

    public function mutations(Request $request)
    {
        $q = StockMutation::with('product:id,sku')->latest();
        if ($request->filled('type')) {
            $q->where('type', $request->type);
        }
        if ($request->filled('since')) {
            $q->where('created_at', '>=', $request->since);
        }

        return response()->json([
            'data' => $q->limit(200)->get()->map(fn ($m) => [
                'id' => $m->id,
                'sku' => $m->product?->sku,
                'type' => $m->type,
                'qty' => (int) $m->quantity,
                'batch' => $m->batch_number,
                'reference' => $m->reference_type.':'.$m->reference_id,
                'created_at' => $m->created_at?->toIso8601String(),
            ]),
        ]);
    }

    public function aging(Request $request, AgingService $aging)
    {
        $warehouseId = null;
        if ($request->filled('warehouse_code')) {
            $warehouseId = Warehouse::where('code', $request->warehouse_code)->value('id');
        }

        return response()->json([
            'summary' => $aging->summary($warehouseId),
            'data' => $aging->report($warehouseId)->take(200)->values(),
        ]);
    }

    public function scan(Request $request, QrCodeService $qr)
    {
        $data = $request->validate(['code' => 'required|string|max:80']);
        $code = strtoupper(trim($data['code']));
        $row = $qr->resolve($code);
        $entity = $row ? $qr->resolveEntity($code) : null;

        return response()->json([
            'code' => $code,
            'found' => (bool) $row,
            'entity_type' => $row?->entity_type,
            'entity_id' => $row?->entity_id,
            'label' => $row?->label,
            'entity' => $entity ? [
                'id' => $entity->id,
                'code' => $entity->code ?? $entity->sku ?? null,
                'name' => $entity->name ?? null,
            ] : null,
        ]);
    }
}
