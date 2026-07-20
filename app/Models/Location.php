<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Location extends Model
{
    use HasFactory;

    public const TYPE_ZONE = 'zone';
    public const TYPE_RACK = 'rack';
    public const TYPE_BIN = 'bin';

    protected $fillable = [
        'warehouse_id',
        'parent_id',
        'code',
        'name',
        'type',
        'capacity',
        'description',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('code');
    }

    public function productStocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    public function qrCode(): HasOne
    {
        return $this->hasOne(QrCode::class, 'entity_id')
            ->where('entity_type', 'location');
    }

    public function fullPath(): string
    {
        $parts = [$this->code];
        $node = $this->relationLoaded('parent') ? $this->parent : $this->parent()->first();
        $guard = 0;
        while ($node && $guard < 10) {
            array_unshift($parts, $node->code);
            $node = $node->parent;
            $guard++;
        }

        return implode(' / ', $parts);
    }

    public function occupiedQuantity(): int
    {
        return (int) $this->productStocks()->sum('quantity');
    }

    public function remainingCapacity(): ?int
    {
        if ($this->capacity === null) {
            return null;
        }

        return max(0, $this->capacity - $this->occupiedQuantity());
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_ZONE => 'Zona',
            self::TYPE_RACK => 'Rak',
            self::TYPE_BIN => 'Bin',
            default => $type,
        };
    }
}
