<?php

namespace Modules\Projects\Application;

use DomainException;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;

class ProjectMembershipManager
{
    public function add(Project $project, User $user, User $actor): void
    {
        $this->assertAdmin($actor);
        $this->assertEligible($project, $user);

        DB::transaction(function () use ($project, $user): void {
            $now = now();
            $existing = DB::table('project_user')
                ->where('project_id', $project->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                DB::table('project_user')
                    ->where('project_id', $project->id)
                    ->where('user_id', $user->id)
                    ->update([
                        'joined_at' => $now,
                        'removed_at' => null,
                        'updated_at' => $now,
                    ]);

                return;
            }

            DB::table('project_user')->insert([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'joined_at' => $now,
                'removed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }

    public function remove(Project $project, User $user, User $actor): void
    {
        $this->assertAdmin($actor);

        DB::table('project_user')
            ->where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->whereNull('removed_at')
            ->update([
                'removed_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function assertAdmin(User $actor): void
    {
        if (! $actor->isAdmin() || ! $actor->is_active) {
            throw new DomainException('Only an active admin may manage project membership.');
        }
    }

    private function assertEligible(Project $project, User $user): void
    {
        if (! $project->client()->active()->exists()) {
            throw new DomainException('Membership cannot be added to a project with an inactive client.');
        }

        if (! $user->isCustomer() || ! $user->is_active) {
            throw new DomainException('Only active customer users can be project members.');
        }

        if ($user->client_id !== $project->client_id) {
            throw new DomainException('Project members must belong to the same client as the project.');
        }
    }
}
