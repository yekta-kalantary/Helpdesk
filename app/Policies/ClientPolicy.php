<?php

namespace App\Policies;

use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;

class ClientPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() && $user->is_active ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Client $client): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Client $client): bool
    {
        return false;
    }

    public function delete(User $user, Client $client): bool
    {
        return false;
    }
}
