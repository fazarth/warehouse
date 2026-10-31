<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\GoodsIssue;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockReturn;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\ReturnService;
use Illuminate\Http\Request;

class StockReturnController extends Controller
{
    public function __construct(private ReturnService $returns)
    {
    }

    public function index(Request $request)
    {
        $query = StockReturn::with(['warehouse', 'user', 'goodsIssue']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $returns = $query->latest()->paginate(15);

        return view('returns.index', compact('returns'));
    }

    public function create(Request $request)
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $locations = Location::where('type', 'bin')->where('is_active', true)->orderBy('code')->get();
        $issues = GoodsIssue::whereIn('status', ['shipped', 'delivered'])->latest()->limit(50)->get();
        $prefillGi = $request->get('goods_issue_id');

        return view('returns.create', compact('warehouses', 'products', 'suppliers', 'locations', 'issues', 'prefillGi'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'source' => 'required|in:customer,supplier,internal',
            'goods_issue_id' => 'nullable|exists:goods_issues,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'location_id' => 'nullable|exists:locations,id',
            'return_date' => 'required|date',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.location_id' => 'nullable|exists:locations,id',
            'items.*.batch_number' => 'nullable|string|max:50',
            'items.*.expired_date' => 'nullable|date',
            'items.*.condition' => 'nullable|in:good,damaged,expired',
        ]);

        try {
            $return = $this->returns->createWithItems(
                $validated,
                $validated['items'],
                auth()->id()
            );

            AuditLog::log(
                'ops.return.create',
                StockReturn::class,
                $return->id,
                'Return dibuat: '.$return->return_number,
                null,
                [
                    'return_number' => $return->return_number,
                    'source' => $return->source ?? ($validated['source'] ?? null),
                    'items' => count($validated['items']),
                ]
            );

            return redirect()->route('returns.show', $return)
                ->with('success', 'Return '.$return->return_number.' dibuat (draft).');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(StockReturn $return)
    {
        $return->load(['warehouse', 'location', 'user', 'receiver', 'goodsIssue', 'supplier', 'items.product', 'items.location']);

        return view('returns.show', compact('return'));
    }

    public function receive(StockReturn $return)
    {
        try {
            $before = $return->status;
            $this->returns->receive($return, auth()->id());
            AuditLog::log(
                'ops.return.receive',
                StockReturn::class,
                $return->id,
                'Return diterima: '.$return->return_number,
                ['status' => $before],
                ['status' => $return->fresh()->status]
            );

            return back()->with('success', 'Return diterima; stok bin bertambah.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
