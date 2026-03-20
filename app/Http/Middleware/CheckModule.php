<?php

namespace App\Http\Middleware;

use App\Services\ModuleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModule
{
    protected ModuleService $moduleService;

    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        // Check if module is enabled
        if (!$this->moduleService->isEnabled($module)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Module tidak aktif',
                    'module' => $module,
                ], 403);
            }

            abort(403, "Modul '{$module}' tidak tersedia. Silakan hubungi administrator.");
        }

        // Check if user can access module
        if (auth()->check() && !$this->moduleService->canAccess($module, auth()->user())) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Akses ditolak',
                    'module' => $module,
                ], 403);
            }

            abort(403, "Anda tidak memiliki akses ke modul '{$module}'.");
        }

        return $next($request);
    }
}
