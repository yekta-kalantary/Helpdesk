<?php

namespace Modules\Projects\Presentation\Livewire;

use App\Models\Activity;
use DomainException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectLifecycle;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Tasks\Domain\Enums\TaskStatus;

class Show extends Component
{
    use WithPagination;

    #[Locked]
    public int $projectId;

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

        try {
            $lifecycle->complete(Project::query()->findOrFail($this->projectId), $user);
            session()->flash('success', 'پروژه تکمیل شد.');
        } catch (DomainException $e) {
            $this->addError('project', 'تا زمانی که تسک باز وجود دارد، پروژه قابل تکمیل نیست.');
        }
    }

    public function reopen(ProjectLifecycle $lifecycle): void
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->isAdmin(), 403);
        $lifecycle->reopen(Project::query()->findOrFail($this->projectId), $user);
        session()->flash('success', 'پروژه دوباره فعال شد.');
    }

    public function render()
    {
        /** @var User $user */
        $user = auth()->user();

        $project = Project::query()
            ->visibleTo($user)
            ->with(['client:id,name'])
            ->findOrFail($this->projectId);

        $members = $project->activeMembers()
            ->where('users.is_active', true)
            ->orderBy('users.name')
            ->orderBy('users.last_name')
            ->paginate(20, $user->isAdmin()
                ? ['users.id', 'users.name', 'users.last_name', 'users.email', 'users.mobile']
                : ['users.id', 'users.name', 'users.last_name'], 'membersPage');

        $tasks = $project->tasks()
            ->with('assignee:id,name,last_name')
            ->latest('updated_at')
            ->paginate(20, ['*'], 'tasksPage');

        $openTasksCount = $project->tasks()
            ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value])
            ->count();

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
            'activities' => $activities,
            'openTasksCount' => $openTasksCount,
            'isAdmin' => $user->isAdmin(),
        ])->title($project->name);
    }
}
