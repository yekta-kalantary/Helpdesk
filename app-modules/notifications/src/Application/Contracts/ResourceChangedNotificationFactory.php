<?php

namespace Modules\Notifications\Application\Contracts;

use Modules\Notifications\Infrastructure\Notifications\ResourceChangedNotification;

interface ResourceChangedNotificationFactory
{
    /** @param array<string, mixed> $payload */
    public function make(string $title, string $body, string $url, array $payload = []): ResourceChangedNotification;
}
