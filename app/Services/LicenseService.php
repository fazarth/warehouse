<?php

namespace App\Services;

use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Collection;

class LicenseService
{
    protected string $type;
    protected array $packageModules;

    public function __construct()
    {
        $this->type = config('license.type', env('LICENSE_TYPE', 'basic'));
        $this->packageModules = config('packages');
    }

    /**
     * Get current license type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Check if license allows a specific module
     */
    public function allowsModule(string $code): bool
    {
        // Check environment override first
        $envKey = 'MODULE_' . strtoupper($code);
        if (env($envKey) !== null) {
            return filter_var(env($envKey), FILTER_VALIDATE_BOOLEAN);
        }

        // Check package allowance
        $allowedModules = $this->getPackageModules();
        
        return in_array($code, $allowedModules) || in_array('*', $allowedModules);
    }

    /**
     * Get modules allowed by current package
     */
    public function getPackageModules(): array
    {
        return $this->packageModules[$this->type]['modules'] ?? [];
    }

    /**
     * Get package info
     */
    public function getPackageInfo(): array
    {
        return $this->packageModules[$this->type] ?? [];
    }

    /**
     * Check if license is expired
     */
    public function isExpired(): bool
    {
        $expires = env('LICENSE_EXPIRES');
        
        if (!$expires) {
            return false;
        }

        return now()->isAfter($expires);
    }

    /**
     * Get max members limit
     */
    public function getMaxMembers(): ?int
    {
        return $this->packageModules[$this->type]['max_members'] ?? null;
    }

    /**
     * Get all package tiers for display
     */
    public function getAllPackages(): array
    {
        return $this->packageModules;
    }

    /**
     * Check if current package is at least X level
     */
    public function isAtLeast(string $package): bool
    {
        $levels = ['basic' => 1, 'pro' => 2, 'full' => 3];
        return ($levels[$this->type] ?? 0) >= ($levels[$package] ?? 0);
    }
}
