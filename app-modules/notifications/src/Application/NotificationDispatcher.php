<?php

namespace Modules\Notifications\Application;

use Illuminate\Support\Facades\Log;
use Modules\Notifications\Application\Contracts\NotifiableDirectory;
use Modules\Notifications\Infrastructure\Notifications\ResourceChangedNotification;
use Throwable;

class NotificationDispatcher
{
    public function __construct(
        private readonly NotifiableDirectory $notifiables,
    ) {}

    /** @param iterable<int> $recipientIds */
    public function sendToAccountIds(iterable $recipientIds, ResourceChangedNotification $notification, ?int $actorId = null): void
    {
        $recipients = $this->notifiables->findActiveNotifiables($recipientIds);

        if ($actorId !== null) {
            $recipients->forget((int) $actorId);
        }

        $recipients->each(function (object $account, int $accountId) use ($notification): void {
            try {
                $account->notify($notification);
            } catch (Throwable $e) {
                Log::warning('Notification delivery failed.', [
                    'recipient_id' => $accountId,
                    'notification' => $notification::class,
                    'exception' => $e::class,
                ]);
            }
        });
    }
}
