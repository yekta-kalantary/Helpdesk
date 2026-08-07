<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Cross-cutting bindings belong here. Domain bindings live inside modules.
    }

    public function boot(): void
    {
        Gate::before(static function (User $user): ?bool {
            return $user->hasRole('admin') ? true : null;
        });
    }
}
