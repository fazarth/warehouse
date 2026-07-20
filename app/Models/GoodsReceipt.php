<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'supplier_id',
        'purchase_order_id',
        'receipt_date',
        'document_number',
        'notes',
        'status',
        'qc_status',
        'qc_by',
        'qc_at',
        'user_id',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'verified_at' => 'datetime',
        'qc_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function qcUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'qc_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function putawayTasks(): HasMany
    {
        return $this->hasMany(PutawayTask::class);
    }

    public static function generateReceiptNumber(): string
    {
        $prefix = 'GR';
        $date = now()->format('Ymd');
        $last = self::whereDate('created_at', today())->latest()->first();
        $sequence = $last ? intval(substr($last->receipt_number, -4)) + 1 : 1;

        return $prefix.$date.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
