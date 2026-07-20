<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PickLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_issue_id',
        'goods_issue_item_id',
        'product_id',
        'warehouse_id',
        'location_id',
        'product_stock_id',
        'batch_number',
        'lot_number',
        'expired_date',
        'quantity',
        'quantity_picked',
        'status',
        'picked_by',
        'picked_at',
        'notes',
    ];

    protected $casts = [
        'expired_date' => 'date',
        'quantity' => 'integer',
        'quantity_picked' => 'integer',
        'picked_at' => 'datetime',
    ];

    public function goodsIssue(): BelongsTo
    {
        return $this->belongsTo(GoodsIssue::class);
    }

    public function goodsIssueItem(): BelongsTo
    {
        return $this->belongsTo(GoodsIssueItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function productStock(): BelongsTo
    {
        return $this->belongsTo(ProductStock::class);
    }

    public function picker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'picked_by');
    }

    public function packageItems(): HasMany
    {
        return $this->hasMany(PackageItem::class);
    }

    public function remaining(): int
    {
        return max(0, (int) $this->quantity - (int) $this->quantity_picked);
    }
}
