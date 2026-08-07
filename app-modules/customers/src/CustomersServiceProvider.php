<?php

namespace Modules\Customers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Customers\Domain\Contracts\CustomerPortalAccount;
use Modules\Customers\Domain\Contracts\CustomerRepository;
use Modules\Customers\Infrastructure\EloquentCustomerRepository;
use Modules\Customers\Infrastructure\LaravelCustomerPortalAccount;

class CustomersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CustomerRepository::class, EloquentCustomerRepository::class);
        $this->app->bind(CustomerPortalAccount::class, LaravelCustomerPortalAccount::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'customers');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'customers');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Livewire::addNamespace(
            namespace: 'customers',
            classNamespace: 'Modules\\Customers\\Presentation\\Livewire',
            classPath: __DIR__.'/Presentation/Livewire',
            classViewPath: __DIR__.'/../resources/views',
        );
    }
}
