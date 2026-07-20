<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_number',
        'source',
        'goods_issue_id',
        'supplier_id',
        'warehouse_id',
        'location_id',
        'return_date',
        'status',
        'reason',
        'notes',
        'user_id',
        'received_by',
        'received_at',
    ];

    protected $casts = [
        'return_date' => 'date',
        'received_at' => 'datetime',
    ];

    public function goodsIssue(): BelongsTo
    {
        return $this->belongsTo(GoodsIssue::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockReturnItem::class);
    }

    public static function generateNumber(): string
    {
        $prefix = 'RTN-'.now()->format('Ymd');
        $last = self::where('return_number', 'like', $prefix.'%')->orderByDesc('id')->first();
        $seq = $last ? (int) substr($last->return_number, -4) + 1 : 1;

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
