<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'description',
        'unit',
        'category',
        'min_stock',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_stock' => 'integer',
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(StockMutation::class);
    }

    public function getTotalStockAttribute(): int
    {
        return $this->stocks()->sum('quantity');
    }
}
