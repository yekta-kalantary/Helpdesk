<?php

namespace Modules\Notifications\Infrastructure\Notifications;

use Modules\Notifications\Application\Contracts\ResourceChangedNotificationFactory;

final class DefaultResourceChangedNotificationFactory implements ResourceChangedNotificationFactory
{
    public function make(string $title, string $body, string $url, array $payload = []): ResourceChangedNotification
    {
        return new ResourceChangedNotification($title, $body, $url, $payload);
    }
}
