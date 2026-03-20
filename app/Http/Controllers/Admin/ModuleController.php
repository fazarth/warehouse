<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Services\LicenseService;
use App\Services\ModuleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ModuleController extends Controller
{
    protected ModuleService $moduleService;
    protected LicenseService $licenseService;

    public function __construct(ModuleService $moduleService, LicenseService $licenseService)
    {
        $this->moduleService = $moduleService;
        $this->licenseService = $licenseService;
    }

    /**
     * Show module management page
     */
    public function index()
    {
        $modules = $this->moduleService->getAllModules();
        $packages = $this->licenseService->getAllPackages();
        $currentPackage = $this->licenseService->getType();
        $packageInfo = $this->licenseService->getPackageInfo();

        return view('admin.modules.index', compact(
            'modules',
            'packages',
            'currentPackage',
            'packageInfo'
        ));
    }

    /**
     * Toggle module status
     */
    public function toggle(Request $request, string $code)
    {
        $module = Module::where('code', $code)->firstOrFail();

        // Check if module is core
        if ($module->is_core) {
            return back()->with('error', 'Modul inti tidak bisa dinonaktifkan!');
        }

        // Check license
        if (!$this->licenseService->allowsModule($code)) {
            return back()->with('error', 'Lisensi Anda tidak mencakup modul ini. Upgrade ke paket yang lebih tinggi.');
        }

        // Toggle the status
        $module->update(['is_enabled' => !$module->is_enabled]);

        $status = $module->is_enabled ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Modul {$module->name} berhasil {$status}!");
    }

    /**
     * Change license package
     */
    public function changePackage(Request $request)
    {
        $request->validate([
            'package' => 'required|in:basic,pro,full',
        ]);

        $package = $request->package;

        // Update .env file
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        // Replace LICENSE_TYPE value
        if (preg_match('/LICENSE_TYPE=\w+/', $envContent)) {
            $envContent = preg_replace('/LICENSE_TYPE=\w+/', "LICENSE_TYPE={$package}", $envContent);
        } else {
            $envContent .= "\nLICENSE_TYPE={$package}\n";
        }

        file_put_contents($envPath, $envContent);

        // Clear config cache
        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        // Auto-enable/disable modules based on package
        $this->syncModulesWithPackage($package);

        return back()->with('success', "Paket berhasil diubah ke " . ucfirst($package) . "!");
    }

    /**
     * Sync module enabled status based on package
     */
    protected function syncModulesWithPackage(string $package)
    {
        $packageModules = config('packages')[$package]['modules'] ?? [];

        Module::query()->update(['is_enabled' => false]);

        // Enable modules that are allowed by package
        if (in_array('*', $packageModules)) {
            Module::query()->update(['is_enabled' => true]);
        } else {
            Module::whereIn('code', $packageModules)->update(['is_enabled' => true]);
        }

        // Core modules are always enabled
        Module::where('is_core', true)->update(['is_enabled' => true]);
    }
}
