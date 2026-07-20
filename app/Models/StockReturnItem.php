<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_return_id',
        'product_id',
        'location_id',
        'batch_number',
        'expired_date',
        'quantity',
        'condition',
        'notes',
    ];

    protected $casts = [
        'expired_date' => 'date',
        'quantity' => 'integer',
    ];

    public function stockReturn(): BelongsTo
    {
        return $this->belongsTo(StockReturn::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
