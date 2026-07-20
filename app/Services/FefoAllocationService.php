<?php

namespace App\Services;

use App\Models\GoodsIssue;
use App\Models\GoodsIssueItem;
use App\Models\Package;
use App\Models\PackageItem;
use App\Models\PickLine;
use App\Models\ProductStock;
use App\Models\StockMutation;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FefoAllocationService
{
    /**
     * FEFO candidates: bin stock only, earliest expiry first, null expiry last, then FIFO id.
     */
    public function candidates(int $productId, int $warehouseId, ?string $batchNumber = null)
    {
        return ProductStock::query()
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->whereNotNull('location_id')
            ->where('quantity', '>', 0)
            ->when($batchNumber !== null && $batchNumber !== '', fn ($q) => $q->where('batch_number', $batchNumber))
            ->orderByRaw('CASE WHEN expired_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expired_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
    }

    public function availableQty(int $productId, int $warehouseId, ?string $batchNumber = null): int
    {
        return (int) ProductStock::query()
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->whereNotNull('location_id')
            ->where('quantity', '>', 0)
            ->when($batchNumber !== null && $batchNumber !== '', fn ($q) => $q->where('batch_number', $batchNumber))
            ->sum('quantity');
    }

    /**
     * Allocate FEFO pick lines for a pending GI. No stock deduction yet.
     */
    public function allocate(GoodsIssue $issue, ?int $userId = null): int
    {
        if (! in_array($issue->status, ['pending', 'picking'], true)) {
            throw new RuntimeException('GI harus pending/picking untuk alokasi.');
        }

        return DB::transaction(function () use ($issue, $userId) {
            $issue = GoodsIssue::lockForUpdate()->with('items')->findOrFail($issue->id);

            // Re-allocate only if no open lines or status pending
            if ($issue->pickLines()->where('status', '!=', 'cancelled')->exists() && $issue->status !== 'pending') {
                throw new RuntimeException('Pick lines sudah ada. Batalkan dulu jika ingin realokasi.');
            }

            // Clear previous cancelled/empty allocations when re-running from pending
            $issue->pickLines()->where('status', 'cancelled')->delete();

            $created = 0;

            foreach ($issue->items as $item) {
                $need = (int) $item->quantity - (int) $item->quantity_allocated;
                if ($need <= 0) {
                    continue;
                }

                $stocks = $this->candidates(
                    (int) $item->product_id,
                    (int) $item->warehouse_id,
                    $item->batch_number
                );

                $allocated = 0;
                foreach ($stocks as $stock) {
                    if ($need <= 0) {
                        break;
                    }
                    $take = min($need, (int) $stock->quantity);
                    if ($take <= 0) {
                        continue;
                    }

                    PickLine::create([
                        'goods_issue_id' => $issue->id,
                        'goods_issue_item_id' => $item->id,
                        'product_id' => $item->product_id,
                        'warehouse_id' => $item->warehouse_id,
                        'location_id' => $stock->location_id,
                        'product_stock_id' => $stock->id,
                        'batch_number' => $stock->batch_number,
                        'lot_number' => $stock->lot_number,
                        'expired_date' => $stock->expired_date,
                        'quantity' => $take,
                        'quantity_picked' => 0,
                        'status' => 'allocated',
                    ]);

                    $allocated += $take;
                    $need -= $take;
                    $created++;
                }

                if ($need > 0) {
                    throw new RuntimeException(
                        'Stok bin tidak cukup (FEFO) untuk item #'.$item->id.
                        ' sisa kurang '.$need.'. Available bin: '.$this->availableQty(
                            (int) $item->product_id,
                            (int) $item->warehouse_id,
                            $item->batch_number
                        )
                    );
                }

                $item->update([
                    'quantity_allocated' => (int) $item->quantity_allocated + $allocated,
                ]);
            }

            $issue->update([
                'status' => 'picking',
                'strategy' => 'fefo',
                'allocated_by' => $userId ?? auth()->id(),
                'allocated_at' => now(),
            ]);

            return $created;
        });
    }

    /**
     * Confirm all allocated lines as picked (or partial via quantities).
     */
    public function completePicking(GoodsIssue $issue, array $lineQtys = [], ?int $userId = null): void
    {
        if (! in_array($issue->status, ['picking'], true)) {
            throw new RuntimeException('GI harus status picking.');
        }

        DB::transaction(function () use ($issue, $lineQtys, $userId) {
            $issue = GoodsIssue::lockForUpdate()->with(['items', 'pickLines'])->findOrFail($issue->id);
            $uid = $userId ?? auth()->id();

            foreach ($issue->pickLines->where('status', 'allocated') as $line) {
                $qty = array_key_exists($line->id, $lineQtys)
                    ? (int) $lineQtys[$line->id]
                    : (int) $line->quantity;

                if ($qty < 0 || $qty > (int) $line->quantity) {
                    throw new RuntimeException('Qty pick invalid untuk line #'.$line->id);
                }

                if ($qty === 0) {
                    $line->update(['status' => 'cancelled', 'notes' => 'zero pick']);
                    continue;
                }

                $line->update([
                    'quantity_picked' => $qty,
                    'status' => 'picked',
                    'picked_by' => $uid,
                    'picked_at' => now(),
                ]);

                $item = $issue->items->firstWhere('id', $line->goods_issue_item_id);
                if ($item) {
                    $item->increment('quantity_picked', $qty);
                }
            }

            $open = $issue->pickLines()->where('status', 'allocated')->exists();
            if ($open) {
                return;
            }

            $pickedAny = $issue->pickLines()->where('status', 'picked')->exists();
            if (! $pickedAny) {
                throw new RuntimeException('Tidak ada line yang di-pick.');
            }

            $issue->update([
                'status' => 'packing',
                'picked_by' => $uid,
                'picked_at' => now(),
            ]);
        });
    }

    /**
     * Pack all picked lines into one package (KISS single carton).
     */
    public function pack(GoodsIssue $issue, ?string $notes = null, ?int $userId = null): Package
    {
        if ($issue->status !== 'packing') {
            throw new RuntimeException('GI harus status packing.');
        }

        return DB::transaction(function () use ($issue, $notes, $userId) {
            $issue = GoodsIssue::lockForUpdate()->with(['pickLines', 'items'])->findOrFail($issue->id);
            $picked = $issue->pickLines->where('status', 'picked');
            if ($picked->isEmpty()) {
                throw new RuntimeException('Tidak ada pick line siap pack.');
            }

            $package = Package::create([
                'package_number' => Package::generateNumber(),
                'goods_issue_id' => $issue->id,
                'notes' => $notes,
                'user_id' => $userId ?? auth()->id(),
            ]);

            foreach ($picked as $line) {
                PackageItem::create([
                    'package_id' => $package->id,
                    'pick_line_id' => $line->id,
                    'product_id' => $line->product_id,
                    'quantity' => (int) $line->quantity_picked,
                ]);

                $item = $issue->items->firstWhere('id', $line->goods_issue_item_id);
                if ($item) {
                    $item->increment('quantity_packed', (int) $line->quantity_picked);
                }
            }

            $issue->update([
                'status' => 'packed',
                'packed_by' => $userId ?? auth()->id(),
                'packed_at' => now(),
            ]);

            return $package->load('items');
        });
    }

    /**
     * Ship: deduct bin stock per picked lines + ledger issue.
     */
    public function ship(GoodsIssue $issue, ?string $carrier = null, ?string $tracking = null, ?int $userId = null): void
    {
        if ($issue->status !== 'packed') {
            throw new RuntimeException('GI harus packed sebelum ship. Status: '.$issue->status);
        }

        DB::transaction(function () use ($issue, $carrier, $tracking, $userId) {
            $issue = GoodsIssue::lockForUpdate()->with('pickLines')->findOrFail($issue->id);
            $uid = $userId ?? auth()->id();

            foreach ($issue->pickLines->where('status', 'picked') as $line) {
                $qty = (int) $line->quantity_picked;
                if ($qty <= 0) {
                    continue;
                }

                $stock = ProductStock::where('id', $line->product_stock_id)
                    ->lockForUpdate()
                    ->first();

                if (! $stock) {
                    $stock = ProductStock::where([
                        'product_id' => $line->product_id,
                        'warehouse_id' => $line->warehouse_id,
                        'location_id' => $line->location_id,
                        'batch_number' => $line->batch_number,
                    ])->lockForUpdate()->first();
                }

                if (! $stock || $stock->quantity < $qty) {
                    throw new RuntimeException(
                        'Stok bin kurang untuk pick line #'.$line->id.
                        ' ('.$line->location_id.'/'.$line->batch_number.')'
                    );
                }

                $stock->decrement('quantity', $qty);

                StockMutation::create([
                    'product_id' => $line->product_id,
                    'from_warehouse_id' => $line->warehouse_id,
                    'to_warehouse_id' => null,
                    'batch_number' => $line->batch_number,
                    'quantity' => $qty,
                    'type' => 'issue',
                    'reference_type' => 'goods_issue',
                    'reference_id' => $issue->id,
                    'notes' => 'Ship '.$issue->issue_number.' bin#'.$line->location_id.' FEFO',
                    'user_id' => $uid,
                ]);
            }

            $issue->update([
                'status' => 'shipped',
                'shipped_by' => $uid,
                'shipped_at' => now(),
                'carrier' => $carrier,
                'tracking_number' => $tracking,
            ]);
        });
    }
}
