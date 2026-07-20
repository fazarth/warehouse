<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PutawayTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_number',
        'goods_receipt_id',
        'goods_receipt_item_id',
        'product_id',
        'warehouse_id',
        'suggested_location_id',
        'location_id',
        'batch_number',
        'lot_number',
        'expired_date',
        'quantity',
        'quantity_done',
        'status',
        'notes',
        'user_id',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'expired_date' => 'date',
        'quantity' => 'integer',
        'quantity_done' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function goodsReceiptItem(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function suggestedLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'suggested_location_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public static function generateNumber(): string
    {
        $prefix = 'PA-'.now()->format('Ymd');
        $last = self::where('task_number', 'like', $prefix.'%')->orderByDesc('id')->first();
        $seq = $last ? (int) substr($last->task_number, -4) + 1 : 1;

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
