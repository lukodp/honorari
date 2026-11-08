<?php

namespace App\Providers;

use App\Models\Organization;
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
        View::composer('partials.app-header', function ($view): void {
            $view->with([
                'activeOrganization' => Organization::query()
                    ->where('is_active', true)
                    ->first(),
                'hasOrganizations' => Organization::exists(),
            ]);
        });
    }
}
