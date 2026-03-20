<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'issue_number',
        'destination',
        'issue_date',
        'delivery_note',
        'notes',
        'status',
        'user_id',
        'shipped_by',
        'shipped_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'shipped_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shipper(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shipped_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsIssueItem::class);
    }

    public static function generateIssueNumber(): string
    {
        $prefix = 'GI';
        $date = now()->format('Ymd');
        $last = self::whereDate('created_at', today())->latest()->first();
        $sequence = $last ? intval(substr($last->issue_number, -4)) + 1 : 1;
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
