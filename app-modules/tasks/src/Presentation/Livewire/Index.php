<?php

namespace Modules\Tasks\Presentation\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Projects\Infrastructure\Models\ProjectTaskStatus;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Infrastructure\Models\Task;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $q = '';

    #[Url(as: 'project', except: '')]
    public string $project = '';

    #[Url(as: 'status', except: '')]
    public string $status = '';

    #[Url(as: 'priority', except: '')]
    public string $priority = '';

    #[Url(as: 'assignee', except: '')]
    public string $assignee = '';

    #[Url(as: 'overdue', except: '')]
    public string $overdue = '';

    #[Url(as: 'sort', except: 'updated_desc')]
    public string $sort = 'updated_desc';

    public function mount(): void
    {
        if ($project = request()->query('project')) {
            $this->project = (string) $project;
        }
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['q', 'project', 'status', 'priority', 'assignee', 'overdue', 'sort'], true)) {
            if ($property === 'project') {
                $this->status = '';
            }
            $this->resetPage();
        }
    }

    public function render()
    {
        /** @var User $user */
        $user = auth()->user();
        $term = trim($this->q);
        $base = Task::query()->visibleTo($user);

        $tasks = (clone $base)
            ->with([
                'project:id,client_id,name,status',
                'projectStatus:id,project_id,title,is_done,is_active',
                'workGroup:id,title',
                'assignee:id,name,last_name',
            ])
            ->when($term !== '', fn ($query) => $query->where(fn ($nested) => $nested
                ->where('title', 'like', "%{$term}%")
                ->orWhere('reference', 'like', "%{$term}%")))
            ->when($this->project !== '', fn ($query) => $query->where('project_id', (int) $this->project))
            ->when($this->status !== '', fn ($query) => $query->where('project_status_id', (int) $this->status))
            ->when($this->priority !== '', fn ($query) => $query->where('priority', $this->priority))
            ->when($this->assignee === 'unassigned', fn ($query) => $query->whereNull('assigned_to'))
            ->when($this->assignee !== '' && $this->assignee !== 'unassigned', fn ($query) => $query->where('assigned_to', (int) $this->assignee))
            ->when($this->overdue === '1', fn ($query) => $query->overdue())
            ->when($this->sort === 'due_asc', fn ($query) => $query->orderByRaw('due_date IS NULL, due_date ASC')->orderByDesc('updated_at'))
            ->when($this->sort === 'due_desc', fn ($query) => $query->orderByRaw('due_date IS NULL, due_date DESC')->orderByDesc('updated_at'))
            ->when(! in_array($this->sort, ['due_asc', 'due_desc'], true), fn ($query) => $query->orderByDesc('updated_at'))
            ->paginate(20);

        $projects = Project::query()->visibleTo($user)->orderBy('name')->get(['id', 'name']);
        $selectedProject = $this->project !== '' ? $projects->firstWhere('id', (int) $this->project) : null;
        $statuses = $selectedProject
            ? ProjectTaskStatus::query()->where('project_id', $selectedProject->id)->active()->orderBy('position')->get(['id', 'title', 'is_done'])
            : collect();

        $assigneeIds = (clone $base)->whereNotNull('assigned_to')->distinct()->pluck('assigned_to');
        $assignees = User::query()
            ->whereIn('id', $assigneeIds)
            ->orderBy('name')
            ->orderBy('last_name')
            ->get(['id', 'name', 'last_name']);

        return view('tasks::index', [
            'tasks' => $tasks,
            'projects' => $projects,
            'assignees' => $assignees,
            'statuses' => $statuses,
            'priorities' => TaskPriority::cases(),
            'isAdmin' => $user->isAdmin(),
        ])->title(__('tasks::messages.tasks'));
    }
}
