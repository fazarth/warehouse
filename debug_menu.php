<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Services\LicenseService;

$user = User::where('email', 'superadmin@system.local')->first();
$ls = app(LicenseService::class);

echo "=== MENU DEBUG ===" . PHP_EOL . PHP_EOL;

echo "User: " . $user->name . " (" . $user->email . ")" . PHP_EOL;
echo "Roles: " . $user->getRoleNames()->implode(', ') . PHP_EOL . PHP_EOL;

echo "Permission Check:" . PHP_EOL;
echo "  shu.view: " . ($user->can('shu.view') ? '✓ YES' : '✗ NO') . PHP_EOL;
echo "  finance.view: " . ($user->can('finance.view') ? '✓ YES' : '✗ NO') . PHP_EOL;
echo "  inventory.view: " . ($user->can('inventory.view') ? '✓ YES' : '✗ NO') . PHP_EOL . PHP_EOL;

echo "License Check (Current: " . $ls->getType() . "):" . PHP_EOL;
echo "  shu allowed: " . ($ls->allowsModule('shu') ? '✓ YES' : '✗ NO') . PHP_EOL;
echo "  finance allowed: " . ($ls->allowsModule('finance') ? '✓ YES' : '✗ NO') . PHP_EOL;
echo "  inventory allowed: " . ($ls->allowsModule('inventory') ? '✓ YES' : '✗ NO') . PHP_EOL . PHP_EOL;

echo "Route Check:" . PHP_EOL;
echo "  shu.index: " . (\Route::has('shu.index') ? '✓ EXISTS' : '✗ MISSING') . PHP_EOL;
echo "  finance.index: " . (\Route::has('finance.index') ? '✓ EXISTS' : '✗ MISSING') . PHP_EOL;
