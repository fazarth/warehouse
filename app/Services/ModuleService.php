<?php

namespace App\Services;

use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class ModuleService
{
    protected LicenseService $license;

    public function __construct(LicenseService $license)
    {
        $this->license = $license;
    }

    public function isEnabled(string $code): bool
    {
        return Module::isEnabled($code);
    }

    public function getEnabledModules(): Collection
    {
        return Module::getEnabled();
    }

    public function getAllModules(): Collection
    {
        return Module::orderBy('sort_order')->get()->map(function ($module) {
            $module->is_licensed = $this->license->allowsModule($module->code);
            $module->is_accessible = $module->is_enabled && $module->is_licensed;
            return $module;
        });
    }

    /**
     * Menu items for sidebar — permission-first, then role, then module toggle.
     */
    public function getMenuItems(User $user): array
    {
        $menuConfig = config('menu', []);
        $enabledModules = $this->getEnabledModules()->pluck('code')->toArray();

        return collect($menuConfig)
            ->filter(fn (array $item) => $this->userCanSeeMenuItem($user, $item, $enabledModules))
            ->map(function (array $item) {
                $route = $item['route'] ?? null;
                $item['url'] = ($route && Route::has($route)) ? route($route) : null;
                $item['available'] = $item['url'] !== null;
                return $item;
            })
            ->filter(fn (array $item) => $item['available'])
            ->values()
            ->toArray();
    }

    public function userCanSeeMenuItem(User $user, array $item, ?array $enabledModules = null): bool
    {
        $enabledModules ??= $this->getEnabledModules()->pluck('code')->toArray();

        // Module toggle
        if (!empty($item['module']) && !in_array($item['module'], $enabledModules, true)) {
            return false;
        }

        // Permission gate (preferred)
        $permission = $item['permission'] ?? null;
        if ($permission) {
            $perms = is_array($permission) ? $permission : [$permission];
            $ok = false;
            foreach ($perms as $perm) {
                if ($user->can($perm)) {
                    $ok = true;
                    break;
                }
            }
            if (!$ok) {
                return false;
            }
        }

        // Role gate (secondary / menu visibility)
        $roles = $item['roles'] ?? ['*'];
        if ($roles !== ['*'] && !$user->hasAnyRole($roles)) {
            return false;
        }

        return true;
    }

    public function canAccess(string $moduleCode, User $user): bool
    {
        if (!$this->isEnabled($moduleCode)) {
            return false;
        }

        $menuConfig = config('menu', []);
        $moduleMenu = collect($menuConfig)->firstWhere('module', $moduleCode);

        if (!$moduleMenu) {
            return true;
        }

        return $this->userCanSeeMenuItem($user, $moduleMenu);
    }

    public function toggle(string $code, bool $enabled): bool
    {
        $module = Module::where('code', $code)->first();

        if (!$module || $module->is_core) {
            return false;
        }

        if (!$this->license->allowsModule($code)) {
            return false;
        }

        $module->update(['is_enabled' => $enabled]);
        return true;
    }

    /**
     * Grouped menu for layout: preserves group order from config.
     *
     * @return array<string, array{label: string, items: array<int, array>}>
     */
    public function getGroupedMenu(User $user): array
    {
        $items = $this->getMenuItems($user);
        $grouped = [];

        foreach ($items as $item) {
            $key = $item['group'] ?? 'main';
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'label' => $item['group_label'] ?? ucfirst($key),
                    'items' => [],
                ];
            }
            $grouped[$key]['items'][] = $item;
        }

        return $grouped;
    }
}
