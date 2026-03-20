<?php

namespace App\Helpers;

use App\Services\LicenseService;
use Illuminate\Support\Facades\App;

class ModuleHelper
{
    /**
     * Check if a module is accessible based on license and permission
     */
    public static function isAccessible(string $moduleCode): bool
    {
        $licenseService = App::make(LicenseService::class);
        return $licenseService->allowsModule($moduleCode);
    }

    /**
     * Get allowed modules for current license
     */
    public static function getAllowedModules(): array
    {
        $licenseService = App::make(LicenseService::class);
        return $licenseService->getAllowedModules();
    }
}
