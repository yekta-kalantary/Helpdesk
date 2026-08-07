<?php

namespace Modules\Reports;

use Illuminate\Support\ServiceProvider;

class ReportsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'reports');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'reports');
    }
}
