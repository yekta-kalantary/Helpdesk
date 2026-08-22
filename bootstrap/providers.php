<?php

use App\Providers\AppServiceProvider;
use Modules\Audit\AuditServiceProvider;
use Modules\Clients\ClientsServiceProvider;
use Modules\Notifications\NotificationsServiceProvider;

return [
    AppServiceProvider::class,
    AuditServiceProvider::class,
    ClientsServiceProvider::class,
    NotificationsServiceProvider::class,
];
