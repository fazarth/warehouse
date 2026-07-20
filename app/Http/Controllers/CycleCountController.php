<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\ProductStock;
use App\Models\Stockopname;
use App\Models\StockopnameItem;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CycleCountController extends Controller
{
    public function index(Request $request)
    {
        $counts = Stockopname::with(['warehouse', 'user'])
            ->where('mode', 'cycle')
            ->latest()
            ->paginate(15);

        return view('cycle-counts.index', compact('counts'));
    }

    public function create()
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $locations = Location::where('type', 'bin')->where('is_active', true)->orderBy('code')->get();

        return view('cycle-counts.create', compact('warehouses', 'locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'location_id' => 'nullable|exists:locations,id',
            'stockopname_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $so = DB::transaction(function () use ($validated) {
            $so = Stockopname::create([
                'stockopname_number' => Stockopname::generateNumber(),
                'warehouse_id' => $validated['warehouse_id'],
                'location_id' => $validated['location_id'] ?? null,
                'stockopname_date' => $validated['stockopname_date'],
                'status' => 'draft',
                'mode' => 'cycle',
                'notes' => $validated['notes'] ?? 'Cycle count',
                'user_id' => auth()->id(),
            ]);

            $q = ProductStock::with('product')
                ->where('warehouse_id', $validated['warehouse_id'])
                ->where('quantity', '>', 0);

            if (! empty($validated['location_id'])) {
                $q->where('location_id', $validated['location_id']);
            } else {
                $q->whereNotNull('location_id');
            }

            foreach ($q->get() as $stock) {
                StockopnameItem::create([
                    'stockopname_id' => $so->id,
                    'product_id' => $stock->product_id,
                    'system_qty' => $stock->quantity,
                    'physical_qty' => 0,
                    'difference' => 0,
                    'batch_number' => $stock->batch_number,
                    'notes' => $stock->location_id ? 'bin#'.$stock->location_id : null,
                ]);
            }

            return $so;
        });

        return redirect()->route('cycle-counts.show', $so)
            ->with('success', 'Cycle count '.$so->stockopname_number.' dibuat ('.$so->items()->count().' lines).');
    }

    public function show(Stockopname $cycle_count)
    {
        if (($cycle_count->mode ?? 'full') !== 'cycle') {
            return redirect()->route('stockopnames.show', $cycle_count);
        }

        $cycle_count->load(['warehouse', 'user', 'approver', 'items.product']);

        return view('cycle-counts.show', ['count' => $cycle_count]);
    }

    public function updateCounts(Request $request, Stockopname $cycle_count)
    {
        if (($cycle_count->mode ?? 'full') !== 'cycle' || $cycle_count->status !== 'draft') {
            return back()->with('error', 'Cycle count tidak bisa diedit.');
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:stockopname_items,id',
            'items.*.physical_qty' => 'nullable|integer|min:0',
        ]);

        foreach ($validated['items'] as $row) {
            $item = StockopnameItem::where('id', $row['id'])
                ->where('stockopname_id', $cycle_count->id)
                ->first();
            if (! $item) {
                continue;
            }
            $phys = $row['physical_qty'] ?? null;
            $item->update([
                'physical_qty' => $phys,
                'difference' => $phys === null ? null : ((int) $phys - (int) $item->system_qty),
            ]);
        }

        return back()->with('success', 'Hitungan cycle count disimpan.');
    }

    public function submit(Stockopname $cycle_count)
    {
        if (($cycle_count->mode ?? 'full') !== 'cycle' || $cycle_count->status !== 'draft') {
            return back()->with('error', 'Status invalid.');
        }

        $cycle_count->update(['status' => 'pending_approval']);

        return back()->with('success', 'Cycle count submitted (pending_approval).');
    }
}
