<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Identity\Infrastructure\Models\User;

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
