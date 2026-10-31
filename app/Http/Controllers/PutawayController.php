<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Location;
use App\Models\PutawayTask;
use App\Services\PutawayService;
use Illuminate\Http\Request;

class PutawayController extends Controller
{
    public function __construct(private PutawayService $putaway)
    {
    }

    public function index(Request $request)
    {
        $tasks = PutawayTask::with(['product', 'warehouse', 'suggestedLocation', 'location', 'goodsReceipt'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status), fn ($q) => $q->where('status', 'pending'))
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('putaways.index', compact('tasks'));
    }

    public function show(PutawayTask $putaway)
    {
        $putaway->load(['product', 'warehouse', 'suggestedLocation', 'location', 'goodsReceipt', 'goodsReceiptItem']);
        $bins = Location::where('warehouse_id', $putaway->warehouse_id)
            ->where('type', Location::TYPE_BIN)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('putaways.show', compact('putaway', 'bins'));
    }

    public function complete(Request $request, PutawayTask $putaway)
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'quantity' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            if (!empty($validated['notes'])) {
                $putaway->update(['notes' => $validated['notes']]);
            }

            $before = ['status' => $putaway->status, 'location_id' => $putaway->location_id];
            $this->putaway->complete(
                $putaway,
                (int) $validated['location_id'],
                isset($validated['quantity']) ? (int) $validated['quantity'] : null,
                auth()->id()
            );
            $putaway->refresh();
            AuditLog::log(
                'ops.putaway.complete',
                PutawayTask::class,
                $putaway->id,
                'Putaway selesai: '.$putaway->task_number,
                $before,
                [
                    'status' => $putaway->status,
                    'location_id' => $putaway->location_id,
                    'quantity' => $validated['quantity'] ?? null,
                ]
            );

            return redirect()->route('putaways.index')
                ->with('success', 'Putaway '.$putaway->task_number.' selesai.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
