<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\Location;
use App\Models\ProductStock;
use App\Models\PutawayTask;
use App\Models\StockMutation;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PutawayService
{
    /**
     * Suggest an active bin in warehouse: prefer bin already holding product, else lowest occupancy.
     */
    public function suggestLocation(int $warehouseId, int $productId, int $qty = 1): ?Location
    {
        $existing = ProductStock::query()
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->whereNotNull('location_id')
            ->where('quantity', '>', 0)
            ->orderByDesc('quantity')
            ->first();

        if ($existing?->location_id) {
            $loc = Location::where('id', $existing->location_id)
                ->where('type', Location::TYPE_BIN)
                ->where('is_active', true)
                ->first();
            if ($loc && $this->hasCapacity($loc, $qty)) {
                return $loc;
            }
        }

        $bins = Location::query()
            ->where('warehouse_id', $warehouseId)
            ->where('type', Location::TYPE_BIN)
            ->where('is_active', true)
            ->withSum('productStocks as occupied', 'quantity')
            ->orderBy('code')
            ->get();

        $best = null;
        $bestScore = PHP_INT_MAX;

        foreach ($bins as $bin) {
            $occupied = (int) ($bin->occupied ?? 0);
            if (!$this->hasCapacity($bin, $qty, $occupied)) {
                continue;
            }
            $score = $bin->capacity ? ($occupied / max(1, $bin->capacity)) : $occupied;
            if ($score < $bestScore) {
                $bestScore = $score;
                $best = $bin;
            }
        }

        return $best;
    }

    public function createTasksFromReceipt(GoodsReceipt $receipt): int
    {
        $receipt->loadMissing('items');
        $created = 0;

        foreach ($receipt->items as $item) {
            $qty = (int) ($item->qty_pass ?? $item->quantity);
            if ($qty <= 0) {
                continue;
            }

            $exists = PutawayTask::where('goods_receipt_item_id', $item->id)
                ->whereIn('status', ['pending', 'done'])
                ->exists();
            if ($exists) {
                continue;
            }

            $suggested = $this->suggestLocation((int) $item->warehouse_id, (int) $item->product_id, $qty);

            PutawayTask::create([
                'task_number' => PutawayTask::generateNumber(),
                'goods_receipt_id' => $receipt->id,
                'goods_receipt_item_id' => $item->id,
                'product_id' => $item->product_id,
                'warehouse_id' => $item->warehouse_id,
                'suggested_location_id' => $suggested?->id,
                'batch_number' => $item->batch_number,
                'lot_number' => $item->lot_number,
                'expired_date' => $item->expired_date,
                'quantity' => $qty,
                'quantity_done' => 0,
                'status' => 'pending',
                'user_id' => auth()->id(),
            ]);
            $created++;
        }

        return $created;
    }

    /**
     * Move staging stock (location_id null) → target bin.
     */
    public function complete(PutawayTask $task, int $locationId, ?int $qty = null, ?int $userId = null): PutawayTask
    {
        if ($task->status === 'done') {
            throw new RuntimeException('Putaway task already completed.');
        }
        if ($task->status === 'cancelled') {
            throw new RuntimeException('Putaway task cancelled.');
        }

        $remaining = (int) $task->quantity - (int) $task->quantity_done;
        $qty = $qty ?? $remaining;
        if ($qty <= 0 || $qty > $remaining) {
            throw new RuntimeException('Invalid putaway quantity.');
        }

        $bin = Location::where('id', $locationId)
            ->where('warehouse_id', $task->warehouse_id)
            ->where('type', Location::TYPE_BIN)
            ->where('is_active', true)
            ->first();

        if (!$bin) {
            throw new RuntimeException('Target must be an active bin in the same warehouse.');
        }

        if (!$this->hasCapacity($bin, $qty)) {
            throw new RuntimeException('Bin capacity exceeded for '.$bin->code);
        }

        return DB::transaction(function () use ($task, $bin, $qty, $userId) {
            // Staging stock (warehouse-level, no bin)
            $staging = ProductStock::where([
                'product_id' => $task->product_id,
                'warehouse_id' => $task->warehouse_id,
                'batch_number' => $task->batch_number,
            ])->whereNull('location_id')->lockForUpdate()->first();

            if (!$staging || $staging->quantity < $qty) {
                throw new RuntimeException('Staging stock insufficient for putaway.');
            }

            $staging->decrement('quantity', $qty);

            $binStock = ProductStock::firstOrCreate(
                [
                    'product_id' => $task->product_id,
                    'warehouse_id' => $task->warehouse_id,
                    'location_id' => $bin->id,
                    'batch_number' => $task->batch_number,
                    'lot_number' => $task->lot_number,
                ],
                [
                    'expired_date' => $task->expired_date,
                    'quantity' => 0,
                ]
            );
            $binStock->increment('quantity', $qty);

            StockMutation::create([
                'product_id' => $task->product_id,
                'from_warehouse_id' => $task->warehouse_id,
                'to_warehouse_id' => $task->warehouse_id,
                'batch_number' => $task->batch_number,
                'quantity' => $qty,
                'type' => 'putaway',
                'reference_type' => 'putaway_task',
                'reference_id' => $task->id,
                'notes' => 'Putaway '.$task->task_number.' → '.$bin->code,
                'user_id' => $userId ?? auth()->id(),
            ]);

            $done = (int) $task->quantity_done + $qty;
            $task->update([
                'location_id' => $bin->id,
                'quantity_done' => $done,
                'status' => $done >= (int) $task->quantity ? 'done' : 'pending',
                'completed_by' => $done >= (int) $task->quantity ? ($userId ?? auth()->id()) : $task->completed_by,
                'completed_at' => $done >= (int) $task->quantity ? now() : $task->completed_at,
            ]);

            return $task->fresh(['product', 'warehouse', 'location', 'suggestedLocation']);
        });
    }

    protected function hasCapacity(Location $bin, int $qty, ?int $occupied = null): bool
    {
        if (!$bin->capacity) {
            return true;
        }
        $occupied = $occupied ?? (int) $bin->productStocks()->sum('quantity');

        return ($occupied + $qty) <= (int) $bin->capacity;
    }
}
