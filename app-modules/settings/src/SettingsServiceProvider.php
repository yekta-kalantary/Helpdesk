<?php

namespace Modules\Settings;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Settings\Infrastructure\Settings\SmtpSettings;
use Throwable;

class SettingsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'settings');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'settings');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Livewire::addNamespace(
            namespace: 'settings',
            classNamespace: 'Modules\\Settings\\Presentation\\Livewire',
            classPath: __DIR__.'/Presentation/Livewire',
            classViewPath: __DIR__.'/../resources/views/livewire',
        );

        try {
            if (! Schema::hasTable('settings')) {
                return;
            }

            $settings = app(SmtpSettings::class);

            config([
                'mail.default' => $settings->enabled ? 'smtp' : 'log',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.scheme' => $settings->scheme ?: null,
                'mail.mailers.smtp.host' => $settings->host ?: '127.0.0.1',
                'mail.mailers.smtp.port' => $settings->port,
                'mail.mailers.smtp.username' => $settings->username,
                'mail.mailers.smtp.password' => $settings->password,
                'mail.from.address' => $settings->from_address,
                'mail.from.name' => $settings->from_name,
            ]);
        } catch (Throwable) {
            // Composer/package discovery can boot before the SQLite file exists,
            // and first-install commands can boot before settings migrations run.
        }
    }
}
