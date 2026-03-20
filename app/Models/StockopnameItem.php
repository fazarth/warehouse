<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockopnameItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stockopname_id',
        'product_id',
        'batch_number',
        'system_qty',
        'physical_qty',
        'difference',
        'notes',
    ];

    protected $casts = [
        'system_qty' => 'integer',
        'physical_qty' => 'integer',
        'difference' => 'integer',
    ];

    public function stockopname(): BelongsTo
    {
        return $this->belongsTo(Stockopname::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
