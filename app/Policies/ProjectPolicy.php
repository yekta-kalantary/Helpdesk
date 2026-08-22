<?php

namespace App\Policies;

use Modules\Identity\Application\AccountAuthenticationEligibility;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;

class ProjectPolicy
{
    public function __construct(private AccountAuthenticationEligibility $eligibility) {}

    public function viewAny(User $user): bool
    {
        return $this->eligibility->canAuthenticateAccount($user->id);
    }

    public function view(User $user, Project $project): bool
    {
        return Project::query()->visibleTo($user)->whereKey($project)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() && $user->is_active;
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isAdmin() && $user->is_active;
    }

    public function delete(User $user, Project $project): bool
    {
        return false;
    }
}
