<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // The application intentionally uses selected lazy-loaded relationships in views.
        // Keep this disabled in the packaged localhost build to avoid development-only runtime exceptions.
        Model::preventLazyLoading(false);
        Paginator::useBootstrapFive();

        Gate::before(function ($user) {
            return $user->role?->role_name === 'main_admin' ? true : null;
        });
    }
}
