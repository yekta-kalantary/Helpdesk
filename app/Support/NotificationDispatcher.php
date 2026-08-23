<?php

namespace App\Support;

use App\Notifications\ResourceChangedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Modules\Identity\Infrastructure\Models\User;
use Throwable;

class NotificationDispatcher
{
    /** @param iterable<User> $recipients */
    public function send(iterable $recipients, ResourceChangedNotification $notification, ?User $actor = null): void
    {
        Collection::make($recipients)
            ->filter(fn (User $user): bool => $user->is_active && $user->id !== $actor?->id)
            ->unique('id')
            ->each(function (User $user) use ($notification): void {
                try {
                    $user->notify($notification);
                } catch (Throwable $e) {
                    Log::warning('Notification delivery failed.', [
                        'recipient_id' => $user->id,
                        'notification' => $notification::class,
                        'exception' => $e::class,
                    ]);
                }
            });
    }

    /** @param iterable<int> $recipientIds */
    public function sendToAccountIds(iterable $recipientIds, ResourceChangedNotification $notification, ?int $actorId = null): void
    {
        $recipients = User::query()->active()->whereIn('id', Collection::make($recipientIds)->unique())->get();

        $this->send($recipients, $notification, $actorId === null ? null : User::query()->find($actorId));
    }
}
