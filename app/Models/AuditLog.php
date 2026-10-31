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
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Persist an audit event. Never throw to callers — audit must not break ops.
     */
    public static function log(
        string $action,
        ?string $modelType = null,
        $modelId = null,
        ?string $description = null,
        $oldValues = null,
        $newValues = null,
        ?int $userId = null
    ): ?self {
        try {
            $ua = request()?->userAgent();
            if ($ua && strlen($ua) > 512) {
                $ua = substr($ua, 0, 512);
            }

            return self::create([
                'user_id' => $userId ?? auth()->id(),
                'action' => $action,
                'model_type' => $modelType,
                'model_id' => $modelId !== null ? (int) $modelId : null,
                'description' => $description,
                'old_values' => self::sanitizePayload($oldValues),
                'new_values' => self::sanitizePayload($newValues),
                'ip_address' => request()?->ip(),
                'user_agent' => $ua,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    protected static function sanitizePayload($payload): ?array
    {
        if ($payload === null) {
            return null;
        }

        if ($payload instanceof Model) {
            $payload = $payload->toArray();
        }

        if (! is_array($payload)) {
            return ['value' => $payload];
        }

        $sensitive = ['password', 'password_confirmation', 'remember_token', 'token', 'secret', 'api_token'];
        $clean = [];
        foreach ($payload as $key => $value) {
            if (in_array(strtolower((string) $key), $sensitive, true)) {
                $clean[$key] = '[redacted]';
                continue;
            }
            if (is_array($value)) {
                $clean[$key] = self::sanitizePayload($value);
            } else {
                $clean[$key] = $value;
            }
        }

        return $clean;
    }

    public function getActionBadgeClassAttribute(): string
    {
        return match (true) {
            str_contains($this->action, 'delete') || str_contains($this->action, 'destroy') => 'bg-red-100 text-red-700',
            str_contains($this->action, 'login') || str_contains($this->action, 'create') || str_contains($this->action, 'store') => 'bg-emerald-100 text-emerald-700',
            str_contains($this->action, 'update') || str_contains($this->action, 'edit') || str_contains($this->action, 'sync') => 'bg-amber-100 text-amber-800',
            str_contains($this->action, 'logout') => 'bg-slate-100 text-slate-700',
            default => 'bg-indigo-50 text-indigo-700',
        };
    }
}
