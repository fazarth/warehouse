<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'location_id',
        'batch_number',
        'lot_number',
        'expired_date',
        'quantity',
    ];

    protected $casts = [
        'expired_date' => 'date',
        'quantity' => 'integer',
    ];

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

    public function isExpired(): bool
    {
        return $this->expired_date && $this->expired_date->isPast();
    }

    public function isNearExpiry(int $days = 30): bool
    {
        return $this->expired_date && $this->expired_date->diffInDays(now()) <= $days;
    }
}
