<?php

namespace App\Services;

use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Collection;

class ModuleService
{
    protected LicenseService $license;

    public function __construct(LicenseService $license)
    {
        $this->license = $license;
    }

    /**
     * Check if a module is enabled
     */
    public function isEnabled(string $code): bool
    {
        return Module::isEnabled($code);
    }

    /**
     * Get all enabled modules
     */
    public function getEnabledModules(): Collection
    {
        return Module::getEnabled();
    }

    /**
     * Get all modules with their status
     */
    public function getAllModules(): Collection
    {
        return Module::orderBy('sort_order')->get()->map(function ($module) {
            $module->is_licensed = $this->license->allowsModule($module->code);
            $module->is_accessible = $module->is_enabled && $module->is_licensed;
            return $module;
        });
    }

    /**
     * Get menu items for a user based on role and enabled modules
     */
    public function getMenuItems(User $user): array
    {
        $menuConfig = config('menu', []);
        $enabledModules = $this->getEnabledModules()->pluck('code')->toArray();
        
        return collect($menuConfig)->filter(function ($item) use ($user, $enabledModules) {
            // Check module is enabled (null means always show)
            if ($item['module'] && !in_array($item['module'], $enabledModules)) {
                return false;
            }

            // Check user has role access
            if ($item['roles'] !== ['*'] && !$user->hasAnyRole($item['roles'])) {
                return false;
            }

            return true;
        })->values()->toArray();
    }

    /**
     * Check if user can access a module
     */
    public function canAccess(string $moduleCode, User $user): bool
    {
        // Check module is enabled
        if (!$this->isEnabled($moduleCode)) {
            return false;
        }

        // Find module config in menu
        $menuConfig = config('menu', []);
        $moduleMenu = collect($menuConfig)->firstWhere('module', $moduleCode);

        if (!$moduleMenu) {
            return true; // No restriction defined
        }

        // Check role
        if ($moduleMenu['roles'] === ['*']) {
            return true;
        }

        return $user->hasAnyRole($moduleMenu['roles']);
    }

    /**
     * Toggle module status
     */
    public function toggle(string $code, bool $enabled): bool
    {
        $module = Module::where('code', $code)->first();
        
        if (!$module || $module->is_core) {
            return false; // Core modules cannot be disabled
        }

        if (!$this->license->allowsModule($code)) {
            return false; // License doesn't allow this module
        }

        $module->update(['is_enabled' => $enabled]);
        return true;
    }

    /**
     * Get menu items grouped by category
     */
    public function getGroupedMenu(User $user): array
    {
        $items = $this->getMenuItems($user);
        
        return collect($items)->groupBy('group')->toArray();
    }
}
