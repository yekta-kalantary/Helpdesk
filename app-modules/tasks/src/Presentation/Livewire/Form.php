<?php

namespace Modules\Tasks\Presentation\Livewire;

use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Projects\Infrastructure\Models\ProjectTaskStatus;
use Modules\Projects\Infrastructure\Models\WorkGroup;
use Modules\Tasks\Application\TaskCollaboration;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Infrastructure\Models\Task;

class Form extends Component
{
    use WithFileUploads;

    #[Locked]
    public ?int $taskId = null;

    public ?int $project_id = null;

    public string $title = '';

    public ?string $description = null;

    public string $project_status_id = '';

    public string $work_group_id = '';

    public string $priority = 'normal';

    public string $assigned_to = '';

    public ?string $due_date = null;

    public ?UploadedFile $attachment = null;

    public function mount(?string $task = null): mixed
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $task) {
            $requestedProject = request()->integer('project');
            $this->project_id = $requestedProject ?: null;

            return null;
        }

        abort_unless($user->isAdmin(), 403);

        $item = ctype_digit($task)
            ? Task::query()->findOrFail((int) $task)
            : Task::query()->where('reference', $task)->firstOrFail();

        if (ctype_digit($task)) {
            return redirect()->route('tasks.edit', $item);
        }

        $item->loadMissing(['project.client', 'projectStatus']);
        abort_if($item->isDone() || ! $item->project->isActive() || ! $item->project->client->isActive(), 403);

        $this->taskId = $item->id;
        $this->project_id = $item->project_id;
        $this->title = $item->title;
        $this->description = $item->description;
        $this->project_status_id = (string) $item->project_status_id;
        $this->work_group_id = $item->work_group_id ? (string) $item->work_group_id : '';
        $this->priority = $item->priority->value;
        $this->assigned_to = $item->assigned_to ? (string) $item->assigned_to : '';
        $this->due_date = $item->due_date?->toDateString();

        return null;
    }

    public function updatedProjectId(): void
    {
        if (! $this->taskId) {
            $this->assigned_to = '';
            $this->project_status_id = '';
            $this->work_group_id = '';
        }
    }

    public function save(TaskWorkflow $workflow, TaskCollaboration $collaboration): mixed
    {
        /** @var User $user */
        $user = auth()->user();

        $rules = [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'project_status_id' => ['nullable', 'integer', Rule::exists('project_task_statuses', 'id')],
            'work_group_id' => ['nullable', 'integer', Rule::exists('work_groups', 'id')],
            'attachment' => [
                'nullable',
                'file',
                'max:'.config('helpdesk.attachments.max_kilobytes', 20480),
                'extensions:'.implode(',', config('helpdesk.attachments.extensions', [])),
                'mimetypes:'.implode(',', config('helpdesk.attachments.mime_types', [])),
            ],
        ];

        if ($user->isAdmin()) {
            $rules += [
                'priority' => ['required', Rule::enum(TaskPriority::class)],
                'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')],
                'due_date' => ['nullable', 'date'],
            ];
        }

        $data = $this->validate($rules);
        $project = Project::query()
            ->visibleTo($user)
            ->where('status', 'active')
            ->findOrFail((int) $data['project_id']);

        $projectStatusId = filled($data['project_status_id'] ?? null) ? (int) $data['project_status_id'] : null;
        $workGroupId = filled($data['work_group_id'] ?? null) ? (int) $data['work_group_id'] : null;

        try {
            if ($this->taskId) {
                abort_unless($user->isAdmin(), 403);
                $task = Task::query()->findOrFail($this->taskId);

                if ($task->project_id !== $project->id) {
                    throw new DomainException('Task project is immutable after creation.');
                }

                $task = $workflow->updateByAdmin($user, $task, [
                    'title' => trim($data['title']),
                    'description' => filled($data['description']) ? trim($data['description']) : null,
                    'project_status_id' => $projectStatusId ?: $task->project_status_id,
                    'work_group_id' => $workGroupId,
                    'priority' => TaskPriority::from($data['priority']),
                    'assigned_to' => $data['assigned_to'] === '' ? null : (int) $data['assigned_to'],
                    'due_date' => $data['due_date'] ?: null,
                ]);
            } else {
                $task = DB::transaction(function () use ($collaboration, $data, $project, $projectStatusId, $user, $workGroupId, $workflow): Task {
                    $payload = [
                        'title' => trim($data['title']),
                        'description' => filled($data['description']) ? trim($data['description']) : null,
                        'project_status_id' => $projectStatusId,
                        'work_group_id' => $workGroupId,
                    ];

                    $task = $user->isAdmin()
                        ? $workflow->createForAdmin($user, $project, $payload + [
                            'priority' => TaskPriority::from($data['priority']),
                            'assigned_to' => $data['assigned_to'] === '' ? null : (int) $data['assigned_to'],
                            'due_date' => $data['due_date'] ?: null,
                        ])
                        : $workflow->createForCustomer($user, $project, $payload);

                    if ($this->attachment) {
                        $collaboration->attach($user, $task, $this->attachment);
                    }

                    return $task;
                });
            }
        } catch (DomainException $e) {
            $this->addError('project_status_id', $e->getMessage());

            return null;
        }

        if ($this->taskId && $this->attachment) {
            $collaboration->attach($user, $task, $this->attachment);
        }

        session()->flash('success', $this->taskId ? __('app.updated_successfully') : __('app.created_successfully'));

        return $this->redirectRoute('tasks.show', ['task' => $task->reference], navigate: true);
    }

    public function render()
    {
        /** @var User $user */
        $user = auth()->user();

        $projects = Project::query()
            ->visibleTo($user)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'client_id', 'name']);

        $project = $this->project_id ? $projects->firstWhere('id', (int) $this->project_id) : null;
        $statuses = collect();
        $workGroups = collect();
        $assignees = collect();

        if ($project) {
            $statuses = ProjectTaskStatus::query()
                ->where('project_id', $project->id)
                ->active()
                ->orderBy('position')
                ->get(['id', 'title', 'is_done', 'position']);
            $workGroups = WorkGroup::query()
                ->where('project_id', $project->id)
                ->active()
                ->orderBy('position')
                ->get(['id', 'parent_id', 'title', 'position']);

            if ($user->isAdmin()) {
                $admins = User::query()->active()->admins()->orderBy('name')->get(['id', 'name', 'last_name', 'role']);
                $customers = $project->activeMembers()
                    ->where('users.is_active', true)
                    ->orderBy('users.name')
                    ->get(['users.id', 'users.name', 'users.last_name', 'users.role']);
                $assignees = $admins->concat($customers)->unique('id')->values();
            }
        }

        $projectName = $this->taskId
            ? Project::query()->whereKey($this->project_id)->value('name')
            : null;

        return view('tasks::form', [
            'projects' => $projects,
            'assignees' => $assignees,
            'statuses' => $statuses,
            'workGroups' => $workGroups,
            'priorities' => TaskPriority::cases(),
            'projectName' => $projectName,
            'isAdmin' => $user->isAdmin(),
        ])->title($this->taskId ? __('tasks::messages.edit_task') : __('tasks::messages.new_task'));
    }
}
