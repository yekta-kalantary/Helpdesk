<?php

namespace Modules\Reports;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class ReportsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'reports');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'reports');

        Livewire::addNamespace(
            namespace: 'reports',
            classNamespace: 'Modules\\Reports\\Presentation\\Livewire',
            classPath: __DIR__.'/Presentation/Livewire',
            classViewPath: __DIR__.'/../resources/views',
        );
    }
}
