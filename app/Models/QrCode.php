<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'entity_type',
        'entity_id',
        'label',
        'meta',
        'is_active',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_active' => 'boolean',
    ];

    public function imageUrl(int $size = 200): string
    {
        $data = rawurlencode($this->code);

        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$data}";
    }
}
