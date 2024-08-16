<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
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

        Blade::if('canDoAction', function ($role, $teamId) {
            return Auth::user()->canDoAction($role, $teamId);
        });
        Blade::if('isAdmin', function () {
            return Auth::user()->isAdmin();
        });
        Blade::if('isAdminOrModerator', function ($team) {
            return Auth::user()->isAdminOrModerator($team);
        });
    }
}
