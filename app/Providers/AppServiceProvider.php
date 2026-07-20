<?php

namespace App\Providers;

use App\Services\ModuleService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            $user = auth()->user();
            $groupedMenu = [];
            if ($user) {
                $groupedMenu = app(ModuleService::class)->getGroupedMenu($user);
            }
            $view->with('sidebarMenu', $groupedMenu);
        });
    }
}
