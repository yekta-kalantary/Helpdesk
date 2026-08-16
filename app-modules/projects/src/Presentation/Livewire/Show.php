<?php

namespace Modules\Projects\Presentation\Livewire;

use App\Models\Activity;
use DomainException;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectLifecycle;
use Modules\Projects\Application\ProjectWorkflowManager;
use Modules\Projects\Application\WorkGroupManager;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Projects\Infrastructure\Models\ProjectTaskStatus;
use Modules\Projects\Infrastructure\Models\WorkGroup;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Infrastructure\Models\Task;

class Show extends Component
{
    use WithPagination;

    #[Locked]
    public int $projectId;

    public string $workGroupFilter = '';

    public string $newStatusTitle = '';

    /** @var array<int, string> */
    public array $statusTitles = [];

    public string $newWorkGroupTitle = '';

    public string $newWorkGroupParentId = '';

    /** @var array<int, string> */
    public array $workGroupTitles = [];

    /** @var array<int, string> */
    public array $workGroupParents = [];

    public function mount(int $project): void
    {
        /** @var User $user */
        $user = auth()->user();
        $this->projectId = Project::query()->visibleTo($user)->findOrFail($project)->id;
    }

    public function complete(ProjectLifecycle $lifecycle): void
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->isAdmin(), 403);

        $this->runDomainAction(function () use ($lifecycle, $user): void {
            $lifecycle->complete($this->project(), $user);
            session()->flash('success', 'پروژه تکمیل شد.');
        });
    }

    public function reopen(ProjectLifecycle $lifecycle): void
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->isAdmin(), 403);

        $this->runDomainAction(function () use ($lifecycle, $user): void {
            $lifecycle->reopen($this->project(), $user);
            session()->flash('success', 'پروژه دوباره فعال شد.');
        });
    }

    public function moveTask(int $taskId, int $statusId, TaskWorkflow $workflow): void
    {
        /** @var User $user */
        $user = auth()->user();

        $this->runDomainAction(function () use ($taskId, $statusId, $user, $workflow): void {
            $project = $this->project();
            $task = Task::query()->visibleTo($user)->where('project_id', $project->id)->findOrFail($taskId);
            $status = ProjectTaskStatus::query()->where('project_id', $project->id)->active()->findOrFail($statusId);
            $workflow->changeStatus($user, $task, $status);
        });
    }

    public function createStatus(ProjectWorkflowManager $manager): void
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->isAdmin(), 403);

        $this->runDomainAction(function () use ($manager, $user): void {
            $manager->create($user, $this->project(), $this->newStatusTitle);
            $this->newStatusTitle = '';
        });
    }

    public function renameStatus(int $statusId, ProjectWorkflowManager $manager): void
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->isAdmin(), 403);

        $this->runDomainAction(function () use ($manager, $statusId, $user): void {
            $status = $this->status($statusId);
            $manager->rename($user, $status, $this->statusTitles[$statusId] ?? $status->title);
        });
    }

    public function setDoneStatus(int $statusId, ProjectWorkflowManager $manager): void
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->isAdmin(), 403);

        $this->runDomainAction(fn () => $manager->setDone($user, $this->status($statusId)));
    }

    public function inactivateStatus(int $statusId, ProjectWorkflowManager $manager): void
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->isAdmin(), 403);

        $this->runDomainAction(fn () => $manager->inactivate($user, $this->status($statusId)));
    }

    public function moveStatus(int $statusId, string $direction, ProjectWorkflowManager $manager): void
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->isAdmin(), 403);

        $this->runDomainAction(function () use ($direction, $manager, $statusId, $user): void {
            $project = $this->project();
            $ids = $project->taskStatuses()->active()->orderBy('position')->orderBy('id')->pluck('id')->all();
            $index = array_search($statusId, $ids, true);
            if ($index === false) {
                throw new DomainException('Project Status not found.');
            }

            $target = $direction === 'up' ? $index - 1 : $index + 1;
            if (! isset($ids[$target])) {
                return;
            }

            [$ids[$index], $ids[$target]] = [$ids[$target], $ids[$index]];
            $manager->reorder($user, $project, $ids);
        });
    }

    public function createWorkGroup(WorkGroupManager $manager): void
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->isAdmin(), 403);

        $this->runDomainAction(function () use ($manager, $user): void {
            $manager->create($user, $this->project(), [
                'title' => $this->newWorkGroupTitle,
                'parent_id' => $this->newWorkGroupParentId !== '' ? (int) $this->newWorkGroupParentId : null,
            ]);
            $this->newWorkGroupTitle = '';
            $this->newWorkGroupParentId = '';
        });
    }

    public function renameWorkGroup(int $workGroupId, WorkGroupManager $manager): void
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->isAdmin(), 403);

        $this->runDomainAction(function () use ($manager, $user, $workGroupId): void {
            $group = $this->workGroup($workGroupId);
            $manager->update($user, $group, ['title' => $this->workGroupTitles[$workGroupId] ?? $group->title]);
        });
    }

    public function moveWorkGroup(int $workGroupId, WorkGroupManager $manager): void
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->isAdmin(), 403);

        $this->runDomainAction(function () use ($manager, $user, $workGroupId): void {
            $group = $this->workGroup($workGroupId);
            $parentId = $this->workGroupParents[$workGroupId] ?? '';
            $parent = $parentId !== '' ? $this->workGroup((int) $parentId) : null;
            $manager->move($user, $group, $parent);
        });
    }

    public function moveWorkGroupPosition(int $workGroupId, string $direction, WorkGroupManager $manager): void
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->isAdmin(), 403);

        $this->runDomainAction(function () use ($direction, $manager, $user, $workGroupId): void {
            $group = $this->workGroup($workGroupId);
            $ids = WorkGroup::query()
                ->where('project_id', $this->projectId)
                ->where('parent_id', $group->parent_id)
                ->active()
                ->orderBy('position')
                ->orderBy('id')
                ->pluck('id')
                ->all();
            $index = array_search($workGroupId, $ids, true);
            if ($index === false) {
                throw new DomainException('Work Group not found.');
            }

            $target = $direction === 'up' ? $index - 1 : $index + 1;
            if (! isset($ids[$target])) {
                return;
            }

            [$ids[$index], $ids[$target]] = [$ids[$target], $ids[$index]];
            $manager->reorder($user, $this->project(), $ids);
        });
    }

    public function inactivateWorkGroup(int $workGroupId, WorkGroupManager $manager): void
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->isAdmin(), 403);

        $this->runDomainAction(fn () => $manager->inactivate($user, $this->workGroup($workGroupId)));
    }

    public function render()
    {
        /** @var User $user */
        $user = auth()->user();

        $project = Project::query()
            ->visibleTo($user)
            ->with(['client:id,name'])
            ->findOrFail($this->projectId);

        $statuses = $project->taskStatuses()->active()->orderBy('position')->orderBy('id')->get();
        foreach ($statuses as $status) {
            $this->statusTitles[$status->id] ??= $status->title;
        }

        $activeWorkGroups = WorkGroup::query()
            ->where('project_id', $project->id)
            ->active()
            ->with('parent')
            ->orderBy('position')
            ->orderBy('id')
            ->get();
        $workGroups = $this->flattenWorkGroups($activeWorkGroups);
        foreach ($workGroups as $group) {
            $this->workGroupTitles[$group->id] ??= $group->title;
            $this->workGroupParents[$group->id] ??= $group->parent_id ? (string) $group->parent_id : '';
        }

        $tasks = $project->tasks()
            ->with([
                'assignee:id,name,last_name',
                'projectStatus:id,title,is_done,position',
                'workGroup:id,title,parent_id',
            ])
            ->when($this->workGroupFilter === 'root', fn ($query) => $query->whereNull('work_group_id'))
            ->when(
                $this->workGroupFilter !== '' && $this->workGroupFilter !== 'root',
                fn ($query) => $query->where('work_group_id', (int) $this->workGroupFilter),
            )
            ->orderByDesc('updated_at')
            ->get();

        $members = $project->activeMembers()
            ->where('users.is_active', true)
            ->orderBy('users.name')
            ->orderBy('users.last_name')
            ->paginate(20, $user->isAdmin()
                ? ['users.id', 'users.name', 'users.last_name', 'users.email', 'users.mobile']
                : ['users.id', 'users.name', 'users.last_name'], 'membersPage');

        $activities = Activity::query()
            ->where('project_id', $project->id)
            ->when(! $user->isAdmin(), fn ($query) => $query->withoutModeration())
            ->with('actor:id,name,last_name')
            ->latest('id')
            ->paginate(20, ['*'], 'projectActivitiesPage');

        return view('projects::show', [
            'project' => $project,
            'members' => $members,
            'tasks' => $tasks,
            'statuses' => $statuses,
            'workGroups' => $workGroups,
            'activities' => $activities,
            'openTasksCount' => $project->tasks()->whereHas('projectStatus', fn ($query) => $query->where('is_done', false))->count(),
            'isAdmin' => $user->isAdmin(),
        ])->title($project->name);
    }

    private function project(): Project
    {
        /** @var User $user */
        $user = auth()->user();

        return Project::query()->visibleTo($user)->findOrFail($this->projectId);
    }

    private function status(int $statusId): ProjectTaskStatus
    {
        return ProjectTaskStatus::query()->where('project_id', $this->projectId)->findOrFail($statusId);
    }

    private function workGroup(int $workGroupId): WorkGroup
    {
        return WorkGroup::query()->where('project_id', $this->projectId)->findOrFail($workGroupId);
    }

    private function runDomainAction(callable $action): void
    {
        $this->resetErrorBag('project');

        try {
            $action();
        } catch (DomainException $e) {
            $this->addError('project', $e->getMessage());
        }
    }

    /** @param Collection<int, WorkGroup> $groups */
    private function flattenWorkGroups(Collection $groups, ?int $parentId = null, int $depth = 1): Collection
    {
        $result = collect();

        foreach ($groups->where('parent_id', $parentId)->sortBy([['position', 'asc'], ['id', 'asc']]) as $group) {
            $group->setAttribute('display_depth', $depth);
            $result->push($group);
            $result = $result->concat($this->flattenWorkGroups($groups, $group->id, $depth + 1));
        }

        return $result->values();
    }
}
