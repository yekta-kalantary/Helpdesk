<?php

namespace Modules\Contacts;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Contacts\Domain\Contracts\ContactRepository;
use Modules\Contacts\Infrastructure\EloquentContactRepository;

class ContactsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ContactRepository::class, EloquentContactRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'contacts');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'contacts');

        Livewire::addNamespace(
            namespace: 'contacts',
            classNamespace: 'Modules\\Contacts\\Presentation\\Livewire',
            classPath: __DIR__.'/Presentation/Livewire',
            classViewPath: __DIR__.'/../resources/views',
        );
    }
}
