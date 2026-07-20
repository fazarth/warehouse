<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsIssueItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_issue_id',
        'product_id',
        'warehouse_id',
        'batch_number',
        'quantity',
        'quantity_allocated',
        'quantity_picked',
        'quantity_packed',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'quantity_allocated' => 'integer',
        'quantity_picked' => 'integer',
        'quantity_packed' => 'integer',
    ];

    public function goodsIssue(): BelongsTo
    {
        return $this->belongsTo(GoodsIssue::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function pickLines(): HasMany
    {
        return $this->hasMany(PickLine::class);
    }
}
