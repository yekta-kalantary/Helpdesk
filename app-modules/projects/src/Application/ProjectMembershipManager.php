<?php

namespace Modules\Projects\Application;

use App\Notifications\ResourceChangedNotification;
use App\Support\ActivityRecorder;
use App\Support\CustomerAssignmentRequeuer;
use App\Support\NotificationDispatcher;
use DomainException;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;

class ProjectMembershipManager
{
    public function __construct(
        private readonly ActivityRecorder $activities,
        private readonly NotificationDispatcher $notifications,
        private readonly CustomerAssignmentRequeuer $assignments,
    ) {}

    public function add(Project $project, User $user, User $actor): void
    {
        $this->assertAdmin($actor);
        $this->assertEligible($project, $user);

        DB::transaction(function () use ($project, $user, $actor): void {
            $project = Project::query()->lockForUpdate()->findOrFail($project->id);
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
            } else {
                DB::table('project_user')->insert([
                    'project_id' => $project->id,
                    'user_id' => $user->id,
                    'joined_at' => $now,
                    'removed_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $this->activities->record($actor, 'membership.added', $project, null, [
                'user_id' => $user->id,
                'joined_at' => $now->toISOString(),
                'reactivated' => $existing !== null,
            ]);
        });

        $this->notifications->send([$user], new ResourceChangedNotification(
            'عضویت پروژه',
            "عضویت شما در پروژه {$project->name} فعال شد.",
            url('/projects/'.$project->id),
            ['resource_type' => 'project', 'resource_id' => $project->id],
        ), $actor);
    }

    public function remove(Project $project, User $user, User $actor): void
    {
        $this->assertAdmin($actor);

        $changed = DB::transaction(function () use ($project, $user, $actor): bool {
            $project = Project::query()->lockForUpdate()->findOrFail($project->id);
            $membership = DB::table('project_user')
                ->where('project_id', $project->id)
                ->where('user_id', $user->id)
                ->whereNull('removed_at')
                ->lockForUpdate()
                ->first();

            if (! $membership) {
                return false;
            }

            $this->assignments->requeue($user, $actor, $project);

            $removedAt = now();
            DB::table('project_user')
                ->where('project_id', $project->id)
                ->where('user_id', $user->id)
                ->whereNull('removed_at')
                ->update([
                    'removed_at' => $removedAt,
                    'updated_at' => $removedAt,
                ]);

            $this->activities->record($actor, 'membership.removed', $project, null, [
                'user_id' => $user->id,
                'removed_at' => $removedAt->toISOString(),
            ]);

            return true;
        });

        if ($changed) {
            $this->notifications->send([$user], new ResourceChangedNotification(
                'تغییر عضویت پروژه',
                "دسترسی شما به پروژه {$project->name} برداشته شد.",
                url('/projects/'.$project->id),
                ['resource_type' => 'project', 'resource_id' => $project->id],
            ), $actor);
        }
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

        if ($user->isCustomer()) {
            if (! $user->is_active) {
                throw new DomainException('Only active customer users can be project members.');
            }
        } elseif ($user->isEmployee()) {
            if (! $user->is_active) {
                throw new DomainException('Only active employee users can be project members.');
            }
        } else {
            throw new DomainException('Only customer or employee users can be project members.');
        }

        if ($user->client_id !== $project->client_id) {
            throw new DomainException('Project members must belong to the same client as the project.');
        }
    }
}
