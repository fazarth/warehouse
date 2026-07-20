<?php

namespace App\Http\Controllers;

use App\Models\GoodsIssue;
use App\Models\GoodsIssueItem;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Warehouse;
use App\Services\FefoAllocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodsIssueController extends Controller
{
    public function __construct(private FefoAllocationService $fefo)
    {
    }

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

        // Available bin stock only (W3 FEFO source)
        $stocks = ProductStock::with(['product', 'warehouse', 'location'])
            ->where('quantity', '>', 0)
            ->whereNotNull('location_id')
            ->get()
            ->groupBy(fn ($s) => $s->product_id.'-'.$s->warehouse_id)
            ->map(fn ($group) => [
                'product_id' => $group->first()->product_id,
                'warehouse_id' => $group->first()->warehouse_id,
                'sku' => $group->first()->product?->sku,
                'name' => $group->first()->product?->name,
                'wh' => $group->first()->warehouse?->code,
                'qty' => $group->sum('quantity'),
            ])
            ->values();

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
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.batch_number' => 'nullable|string|max:50',
        ]);

        try {
            $issue = DB::transaction(function () use ($validated) {
                foreach ($validated['items'] as $item) {
                    $avail = $this->fefo->availableQty(
                        (int) $item['product_id'],
                        (int) $item['warehouse_id'],
                        $item['batch_number'] ?? null
                    );
                    if ($avail < (int) $item['quantity']) {
                        $product = Product::find($item['product_id']);
                        throw new \RuntimeException(
                            "Stok bin {$product?->name} tidak cukup (butuh {$item['quantity']}, ada {$avail})."
                        );
                    }
                }

                $issue = GoodsIssue::create([
                    'issue_number' => GoodsIssue::generateIssueNumber(),
                    'destination' => $validated['destination'],
                    'issue_date' => $validated['issue_date'],
                    'delivery_note' => $validated['delivery_note'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'status' => 'pending',
                    'strategy' => 'fefo',
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

                return $issue;
            });

            return redirect()->route('goods-issues.show', $issue)
                ->with('success', 'DO/GI '.$issue->issue_number.' dibuat. Lanjut alokasi FEFO.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan: '.$e->getMessage());
        }
    }

    public function show(GoodsIssue $goodsIssue)
    {
        $goodsIssue->load([
            'user', 'shipper', 'allocator', 'picker', 'packer',
            'items.product', 'items.warehouse',
            'pickLines.location', 'pickLines.product',
            'packages.items.product',
        ]);

        return view('goods-issues.show', compact('goodsIssue'));
    }

    public function allocate(GoodsIssue $goodsIssue)
    {
        try {
            $n = $this->fefo->allocate($goodsIssue, auth()->id());

            return back()->with('success', "FEFO allocate: {$n} pick line(s).");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function pick(Request $request, GoodsIssue $goodsIssue)
    {
        $validated = $request->validate([
            'lines' => 'nullable|array',
            'lines.*' => 'integer|min:0',
        ]);

        try {
            $this->fefo->completePicking(
                $goodsIssue,
                $validated['lines'] ?? [],
                auth()->id()
            );

            return back()->with('success', 'Picking selesai → packing.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function pack(Request $request, GoodsIssue $goodsIssue)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            $pkg = $this->fefo->pack($goodsIssue, $validated['notes'] ?? null, auth()->id());

            return back()->with('success', 'Packed: '.$pkg->package_number);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function ship(Request $request, GoodsIssue $goodsIssue)
        {
            abort_unless(auth()->user()->can('goods_issue.ship'), 403);

            // Backward-compatible: if still pending, auto-run allocate+pick+pack then ship (smoke-friendly opt-in)
            $validated = $request->validate([
            'carrier' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:100',
            'auto_pipeline' => 'nullable|boolean',
        ]);

        try {
            if ($goodsIssue->status === 'pending' && $request->boolean('auto_pipeline')) {
                $this->fefo->allocate($goodsIssue, auth()->id());
                $goodsIssue->refresh();
                $this->fefo->completePicking($goodsIssue, [], auth()->id());
                $goodsIssue->refresh();
                $this->fefo->pack($goodsIssue, 'auto pipeline', auth()->id());
                $goodsIssue->refresh();
            }

            $this->fefo->ship(
                $goodsIssue->fresh(),
                $validated['carrier'] ?? null,
                $validated['tracking_number'] ?? null,
                auth()->id()
            );

            return back()->with('success', 'Barang shipped; stok bin dikurangi (FEFO).');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal ship: '.$e->getMessage());
        }
    }

    public function deliver(GoodsIssue $goodsIssue)
    {
        if ($goodsIssue->status !== 'shipped') {
            return back()->with('error', 'Status pengiriman tidak valid.');
        }

        $goodsIssue->update(['status' => 'delivered']);

        return back()->with('success', 'Pengiriman dikonfirmasi delivered.');
    }
}
