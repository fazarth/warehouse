<?php

namespace App\Services;

use App\Models\ProductStock;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AgingService
{
    /**
     * Bucket stock by expiry aging.
     * buckets: expired | 0-30 | 31-60 | 61-90 | 90+ | no_expiry
     */
    public function report(?int $warehouseId = null): Collection
    {
        $today = Carbon::today();

        $query = ProductStock::with(['product', 'warehouse', 'location'])
            ->where('quantity', '>', 0);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->get()->map(function (ProductStock $s) use ($today) {
            $bucket = 'no_expiry';
            $days = null;

            if ($s->expired_date) {
                $days = $today->diffInDays($s->expired_date, false);
                if ($days < 0) {
                    $bucket = 'expired';
                } elseif ($days <= 30) {
                    $bucket = '0-30';
                } elseif ($days <= 60) {
                    $bucket = '31-60';
                } elseif ($days <= 90) {
                    $bucket = '61-90';
                } else {
                    $bucket = '90+';
                }
            }

            return [
                'product_id' => $s->product_id,
                'sku' => $s->product?->sku,
                'name' => $s->product?->name,
                'warehouse' => $s->warehouse?->code,
                'location' => $s->location?->code ?? 'STAGING',
                'batch' => $s->batch_number,
                'qty' => (int) $s->quantity,
                'expired_date' => $s->expired_date?->format('Y-m-d'),
                'days_to_expiry' => $days,
                'bucket' => $bucket,
            ];
        })->sortBy([
            ['bucket', 'asc'],
            ['days_to_expiry', 'asc'],
        ])->values();
    }

    public function summary(?int $warehouseId = null): array
    {
        $rows = $this->report($warehouseId);
        $buckets = ['expired', '0-30', '31-60', '61-90', '90+', 'no_expiry'];
        $out = [];
        foreach ($buckets as $b) {
            $subset = $rows->where('bucket', $b);
            $out[$b] = [
                'lines' => $subset->count(),
                'qty' => $subset->sum('qty'),
            ];
        }

        return $out;
    }
}
