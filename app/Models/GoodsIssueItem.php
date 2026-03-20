<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsIssueItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_issue_id',
        'product_id',
        'warehouse_id',
        'batch_number',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
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
}
