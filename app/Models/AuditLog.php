<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'description',
        'ip_address',
    ];

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function log($action, $modelType, $modelId, $description = null, $oldValues = null, $newValues = null)
    {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
        ]);
    }
}
