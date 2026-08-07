<?php

use Modules\Settings\Infrastructure\Settings\SmtpSettings;

return [
    'settings' => [
        SmtpSettings::class,
    ],

    'migrations_paths' => [
        base_path('app-modules/settings/database/settings'),
    ],
];
