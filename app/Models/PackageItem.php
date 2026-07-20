<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'pick_line_id',
        'product_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function pickLine(): BelongsTo
    {
        return $this->belongsTo(PickLine::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
