<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'order_date',
        'expected_date',
        'status',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public static function generateNumber(): string
    {
        $prefix = 'PO-'.now()->format('Ymd');
        $last = self::where('po_number', 'like', $prefix.'%')->orderByDesc('id')->first();
        $seq = $last ? (int) substr($last->po_number, -4) + 1 : 1;

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function refreshStatus(): void
    {
        $ordered = (int) $this->items()->sum('quantity_ordered');
        $received = (int) $this->items()->sum('quantity_received');

        if ($this->status === 'cancelled') {
            return;
        }

        if ($received <= 0) {
            $this->update(['status' => $this->status === 'draft' ? 'draft' : 'ordered']);
        } elseif ($received < $ordered) {
            $this->update(['status' => 'partial']);
        } else {
            $this->update(['status' => 'received']);
        }
    }
}
