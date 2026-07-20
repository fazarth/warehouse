<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'location',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function productStocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function stockopnames(): HasMany
    {
        return $this->hasMany(Stockopname::class);
    }
}
