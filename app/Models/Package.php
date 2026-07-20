<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_number',
        'goods_issue_id',
        'weight',
        'dimensions',
        'notes',
        'user_id',
    ];

    public function goodsIssue(): BelongsTo
    {
        return $this->belongsTo(GoodsIssue::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PackageItem::class);
    }

    public static function generateNumber(): string
    {
        $prefix = 'PKG-'.now()->format('Ymd');
        $last = self::where('package_number', 'like', $prefix.'%')->orderByDesc('id')->first();
        $seq = $last ? (int) substr($last->package_number, -4) + 1 : 1;

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
