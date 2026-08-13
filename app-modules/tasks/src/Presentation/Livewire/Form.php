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
use Modules\Tasks\Application\TaskCollaboration;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Domain\Enums\TaskStatus;
use Modules\Tasks\Infrastructure\Models\Task;

class Form extends Component
{
    use WithFileUploads;

    #[Locked]
    public ?int $taskId = null;

    public ?int $project_id = null;

    public string $title = '';

    public ?string $description = null;

    public string $status = 'waiting_admin';

    public string $priority = 'normal';

    public string $assigned_to = '';

    public ?string $due_date = null;

    public ?UploadedFile $attachment = null;

    public function mount(?int $task = null): void
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $task) {
            $requestedProject = request()->integer('project');
            $this->project_id = $requestedProject ?: null;

            return;
        }

        abort_unless($user->isAdmin(), 403);

        $item = Task::query()->findOrFail($task);
        $this->taskId = $item->id;
        $this->project_id = $item->project_id;
        $this->title = $item->title;
        $this->description = $item->description;
        $this->status = $item->status->value;
        $this->priority = $item->priority->value;
        $this->assigned_to = $item->assigned_to ? (string) $item->assigned_to : '';
        $this->due_date = $item->due_date?->toDateString();
    }

    public function updatedProjectId(): void
    {
        if (! $this->taskId) {
            $this->assigned_to = '';
        }
    }

    public function updatedStatus(): void
    {
        if ($this->status === TaskStatus::WaitingAdmin->value && $this->assigned_to !== '') {
            $assignee = User::query()->find((int) $this->assigned_to);
            if ($assignee?->isCustomer()) {
                $this->assigned_to = '';
            }
        }
    }

    public function save(TaskWorkflow $workflow, TaskCollaboration $collaboration)
    {
        /** @var User $user */
        $user = auth()->user();

        $rules = [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
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
                'status' => ['required', Rule::enum(TaskStatus::class)],
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
                    'status' => TaskStatus::from($data['status']),
                    'priority' => TaskPriority::from($data['priority']),
                    'assigned_to' => $data['assigned_to'] === '' ? null : (int) $data['assigned_to'],
                    'due_date' => $data['due_date'] ?: null,
                ]);
            } else {
                $task = DB::transaction(function () use ($collaboration, $data, $project, $user, $workflow): Task {
                    $task = $user->isAdmin()
                        ? $workflow->createForAdmin($user, $project, [
                            'title' => trim($data['title']),
                            'description' => filled($data['description']) ? trim($data['description']) : null,
                            'status' => TaskStatus::from($data['status']),
                            'priority' => TaskPriority::from($data['priority']),
                            'assigned_to' => $data['assigned_to'] === '' ? null : (int) $data['assigned_to'],
                            'due_date' => $data['due_date'] ?: null,
                        ])
                        : $workflow->createForCustomer($user, $project, [
                            'title' => trim($data['title']),
                            'description' => filled($data['description']) ? trim($data['description']) : null,
                        ]);

                    if ($this->attachment) {
                        $collaboration->attach($user, $task, $this->attachment);
                    }

                    return $task;
                });
            }
        } catch (DomainException $e) {
            $this->addError('status', $this->domainMessage($e));

            return null;
        }

        if ($this->taskId && $this->attachment) {
            $collaboration->attach($user, $task, $this->attachment);
        }

        session()->flash('success', $this->taskId ? __('app.updated_successfully') : __('app.created_successfully'));

        return $this->redirectRoute('tasks.show', ['task' => $task->id], navigate: true);
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

        $project = $this->project_id
            ? $projects->firstWhere('id', (int) $this->project_id)
            : null;

        $assignees = collect();
        if ($user->isAdmin() && $project) {
            $admins = User::query()->active()->admins()->orderBy('name')->get(['id', 'name', 'last_name', 'role']);
            $customers = $project->activeMembers()
                ->where('users.is_active', true)
                ->orderBy('users.name')
                ->get(['users.id', 'users.name', 'users.last_name', 'users.role']);

            $assignees = match ($this->status) {
                TaskStatus::WaitingCustomer->value => $customers,
                TaskStatus::WaitingAdmin->value => $admins,
                default => $admins->concat($customers)->unique('id')->values(),
            };
        }

        $projectName = $this->taskId
            ? Project::query()->whereKey($this->project_id)->value('name')
            : null;

        return view('tasks::form', [
            'projects' => $projects,
            'assignees' => $assignees,
            'statuses' => TaskStatus::cases(),
            'priorities' => TaskPriority::cases(),
            'projectName' => $projectName,
            'isAdmin' => $user->isAdmin(),
        ])->title($this->taskId ? __('tasks::messages.edit_task') : __('tasks::messages.new_task'));
    }

    private function domainMessage(DomainException $e): string
    {
        return match (true) {
            str_contains($e->getMessage(), 'Waiting Customer') => 'وضعیت منتظر مشتری به یک کاربر فعال عضو همین پروژه نیاز دارد.',
            str_contains($e->getMessage(), 'Waiting Admin') => 'در وضعیت منتظر ادمین، مسئول باید ادمین فعال یا خالی باشد.',
            str_contains($e->getMessage(), 'Todo'), str_contains($e->getMessage(), 'In Progress') => 'برای این وضعیت تعیین مسئول فعال الزامی است.',
            default => 'ترکیب وضعیت و مسئول معتبر نیست.',
        };
    }
}
