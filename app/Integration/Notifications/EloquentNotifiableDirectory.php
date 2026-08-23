<?php

namespace App\Integration\Notifications;

use Illuminate\Support\Collection;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Notifications\Application\Contracts\NotifiableDirectory;

final class EloquentNotifiableDirectory implements NotifiableDirectory
{
    public function findActiveNotifiables(iterable $accountIds): Collection
    {
        return User::query()
            ->active()
            ->whereIn('id', Collection::make($accountIds))
            ->get()
            ->keyBy(fn (User $user): int => (int) $user->id);
    }
}
