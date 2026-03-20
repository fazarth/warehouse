<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'icon',
        'is_enabled',
        'is_core',
        'package',
        'sort_order',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_core' => 'boolean',
    ];

    /**
     * Check if module is enabled
     */
    public static function isEnabled(string $code): bool
    {
        $module = static::where('code', $code)->first();
        
        if (!$module) {
            return false;
        }

        // Check license allows this module
        if (!app(\App\Services\LicenseService::class)->allowsModule($code)) {
            return false;
        }

        return $module->is_enabled;
    }

    /**
     * Get all enabled modules
     */
    public static function getEnabled()
    {
        $license = app(\App\Services\LicenseService::class);
        
        return static::where('is_enabled', true)
            ->orderBy('sort_order')
            ->get()
            ->filter(fn($m) => $license->allowsModule($m->code));
    }

    /**
     * Scope for enabled modules
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope for package
     */
    public function scopeForPackage($query, string $package)
    {
        $packages = ['basic' => 1, 'pro' => 2, 'full' => 3];
        $level = $packages[$package] ?? 1;
        
        return $query->whereIn('package', array_keys(array_filter($packages, fn($v) => $v <= $level)));
    }
}
