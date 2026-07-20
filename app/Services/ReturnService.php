<?php

namespace App\Services;

use App\Models\ProductStock;
use App\Models\StockMutation;
use App\Models\StockReturn;
use App\Models\StockReturnItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReturnService
{
    /**
     * Receive return: stock in to bin (or warehouse staging if no location).
     */
    public function receive(StockReturn $return, ?int $userId = null): void
    {
        if ($return->status !== 'draft') {
            throw new RuntimeException('Return harus draft.');
        }

        DB::transaction(function () use ($return, $userId) {
            $return = StockReturn::lockForUpdate()->with('items')->findOrFail($return->id);
            $uid = $userId ?? auth()->id();

            if ($return->items->isEmpty()) {
                throw new RuntimeException('Return tanpa item.');
            }

            foreach ($return->items as $item) {
                if ($item->condition === 'damaged') {
                    // Damaged: ledger only as return+notes, still put to bin if location set (KISS)
                }

                $locationId = $item->location_id ?: $return->location_id;

                $stock = ProductStock::firstOrCreate(
                    [
                        'product_id' => $item->product_id,
                        'warehouse_id' => $return->warehouse_id,
                        'location_id' => $locationId,
                        'batch_number' => $item->batch_number,
                    ],
                    [
                        'quantity' => 0,
                        'lot_number' => null,
                        'expired_date' => $item->expired_date,
                    ]
                );

                if ($item->expired_date && ! $stock->expired_date) {
                    $stock->expired_date = $item->expired_date;
                    $stock->save();
                }

                $stock->increment('quantity', (int) $item->quantity);

                StockMutation::create([
                    'product_id' => $item->product_id,
                    'from_warehouse_id' => null,
                    'to_warehouse_id' => $return->warehouse_id,
                    'batch_number' => $item->batch_number,
                    'quantity' => (int) $item->quantity,
                    'type' => 'return',
                    'reference_type' => 'stock_return',
                    'reference_id' => $return->id,
                    'notes' => 'Return '.$return->return_number.' ('.$item->condition.')',
                    'user_id' => $uid,
                ]);
            }

            $return->update([
                'status' => 'received',
                'received_by' => $uid,
                'received_at' => now(),
            ]);
        });
    }

    public function createWithItems(array $header, array $items, int $userId): StockReturn
    {
        return DB::transaction(function () use ($header, $items, $userId) {
            $return = StockReturn::create([
                'return_number' => StockReturn::generateNumber(),
                'source' => $header['source'] ?? 'customer',
                'goods_issue_id' => $header['goods_issue_id'] ?? null,
                'supplier_id' => $header['supplier_id'] ?? null,
                'warehouse_id' => $header['warehouse_id'],
                'location_id' => $header['location_id'] ?? null,
                'return_date' => $header['return_date'] ?? now()->toDateString(),
                'status' => 'draft',
                'reason' => $header['reason'] ?? null,
                'notes' => $header['notes'] ?? null,
                'user_id' => $userId,
            ]);

            foreach ($items as $row) {
                StockReturnItem::create([
                    'stock_return_id' => $return->id,
                    'product_id' => $row['product_id'],
                    'location_id' => $row['location_id'] ?? $header['location_id'] ?? null,
                    'batch_number' => $row['batch_number'] ?? null,
                    'expired_date' => $row['expired_date'] ?? null,
                    'quantity' => (int) $row['quantity'],
                    'condition' => $row['condition'] ?? 'good',
                    'notes' => $row['notes'] ?? null,
                ]);
            }

            return $return->load('items');
        });
    }
}
