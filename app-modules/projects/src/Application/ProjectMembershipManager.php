<?php

namespace Modules\Projects\Application;

use App\Integration\Outbox\OutboxRecorder;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Clients\Application\Contracts\ClientStatusQuery;
use Modules\Identity\Application\Contracts\AccountDirectory;
use Modules\Identity\Application\DTOs\AccountSummary;
use Modules\Identity\Domain\Enums\UserRole;
use Modules\Projects\Application\Contracts\ProjectMembershipDirectory;
use Modules\Projects\Application\Events\ProjectMembershipRemovedV1;
use Modules\Projects\Infrastructure\Models\Project;

class ProjectMembershipManager
{
    public function __construct(
        private readonly AccountDirectory $accounts,
        private readonly ClientStatusQuery $clients,
        private readonly ProjectMembershipDirectory $memberships,
        private readonly OutboxRecorder $outbox,
    ) {}

    public function add(Project $project, int $accountId, int $actorId): void
    {
        $this->assertAdmin($actorId);
        $this->assertEligible($project->id, $accountId);

        DB::transaction(function () use ($project, $accountId): void {
            $project = Project::query()->lockForUpdate()->findOrFail($project->id);
            $now = now();
            $existing = DB::table('project_user')
                ->where('project_id', $project->id)
                ->where('user_id', $accountId)
                ->first();

            if ($existing) {
                DB::table('project_user')
                    ->where('project_id', $project->id)
                    ->where('user_id', $accountId)
                    ->update([
                        'joined_at' => $now,
                        'removed_at' => null,
                        'updated_at' => $now,
                    ]);
            } else {
                DB::table('project_user')->insert([
                    'project_id' => $project->id,
                    'user_id' => $accountId,
                    'joined_at' => $now,
                    'removed_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    public function remove(Project $project, int $accountId, int $actorId): void
    {
        $this->assertAdmin($actorId);

        DB::transaction(function () use ($project, $accountId, $actorId): void {
            $project = Project::query()->lockForUpdate()->findOrFail($project->id);
            $membership = DB::table('project_user')
                ->where('project_id', $project->id)
                ->where('user_id', $accountId)
                ->whereNull('removed_at')
                ->lockForUpdate()
                ->first();

            if (! $membership) {
                return;
            }

            $removedAt = now();
            DB::table('project_user')
                ->where('project_id', $project->id)
                ->where('user_id', $accountId)
                ->whereNull('removed_at')
                ->update([
                    'removed_at' => $removedAt,
                    'updated_at' => $removedAt,
                ]);

            $this->outbox->record(new ProjectMembershipRemovedV1(
                eventId: (string) Str::uuid(),
                correlationId: (string) Str::uuid(),
                occurredAt: $removedAt->toIso8601String(),
                projectId: $project->id,
                accountId: $accountId,
                actorId: $actorId,
            ));
        });
    }

    private function assertAdmin(int $actorId): AccountSummary
    {
        $actor = $this->accounts->find($actorId);

        if ($actor === null || ! $actor->isActive || $actor->role !== UserRole::Admin) {
            throw new DomainException('Only an active admin may manage project membership.');
        }

        return $actor;
    }

    private function assertEligible(int $projectId, int $accountId): void
    {
        $project = $this->memberships->findProject($projectId);
        $account = $this->accounts->find($accountId);

        if ($project === null || $this->clients->find($project->clientId)?->isActive !== true) {
            throw new DomainException('Membership cannot be added to a project with an inactive client.');
        }

        if ($account === null || ! $account->isActive) {
            throw new DomainException('Only active customer or employee users can be project members.');
        }

        if ($account->role === UserRole::Customer && $account->clientId !== $project->clientId) {
            throw new DomainException('Project members must belong to the same client as the project.');
        }

        if (! in_array($account->role, [UserRole::Customer, UserRole::Employee], true)) {
            throw new DomainException('Only customer or employee users can be project members.');
        }
    }
}
