<?php

namespace Modules\Notifications\Application\Contracts;

use Illuminate\Support\Collection;

interface NotifiableDirectory
{
    /**
     * Resolve account identifiers to their active notifiables.
     *
     * @param  iterable<int>  $accountIds
     * @return Collection<int, object> active notifiables keyed by account id
     */
    public function findActiveNotifiables(iterable $accountIds): Collection;
}
