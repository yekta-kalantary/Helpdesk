<?php

namespace Modules\Settings;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
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

        if (! Schema::hasTable('settings')) {
            return;
        }

        try {
            $settings = app(SmtpSettings::class);
            $password = $settings->password_encrypted
                ? Crypt::decryptString($settings->password_encrypted)
                : null;

            config([
                'mail.default' => $settings->enabled ? 'smtp' : 'log',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.scheme' => $settings->scheme ?: null,
                'mail.mailers.smtp.host' => $settings->host ?: '127.0.0.1',
                'mail.mailers.smtp.port' => $settings->port,
                'mail.mailers.smtp.username' => $settings->username,
                'mail.mailers.smtp.password' => $password,
                'mail.from.address' => $settings->from_address,
                'mail.from.name' => $settings->from_name,
            ]);
        } catch (Throwable) {
            // During first installation/migration, settings may not exist yet.
        }
    }
}
