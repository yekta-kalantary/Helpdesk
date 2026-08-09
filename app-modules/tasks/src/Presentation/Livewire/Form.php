<?php

namespace Modules\Tasks\Presentation\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Tasks\Application\Actions\SaveTask;
use Modules\Tasks\Application\Queries\TaskAccessScope;
use Modules\Tasks\Application\Queries\TaskFormOptions;
use Modules\Tasks\Domain\Contracts\TaskRepository;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Domain\Enums\TaskStatus;

class Form extends Component
{
    use WithFileUploads;

    #[Locked]
    public ?int $taskId = null;

    public ?int $project_id = null;
    public string $title = '';
    public ?string $description = null;
    public ?int $assigned_to = null;
    public string $priority = 'medium';
    public string $status = 'todo';
    public ?string $due_at = null;
    public ?int $estimated_minutes = null;
    public ?int $spent_minutes = null;
    public array $attachments = [];

    protected TaskRepository $tasks;
    protected SaveTask $saveTask;
    protected TaskAccessScope $scopeBuilder;
    protected TaskFormOptions $formOptions;

    public function boot(
        TaskRepository $tasks,
        SaveTask $saveTask,
        TaskAccessScope $scopeBuilder,
        TaskFormOptions $formOptions,
    ): void {
        $this->tasks = $tasks;
        $this->saveTask = $saveTask;
        $this->scopeBuilder = $scopeBuilder;
        $this->formOptions = $formOptions;
    }

    public function mount(?int $task = null): void
    {
        /** @var User $user */
        $user = auth()->user();
        $scope = $this->scopeBuilder->for($user);

        if (! $task) {
            $project = request()->integer('project');
            $this->project_id = $project ?: null;

            return;
        }

        $item = $this->tasks->findAccessible($task, $scope);
        $this->taskId = $task;
        $this->project_id = (int) $item['project_id'];
        $this->title = $item['title'];
        $this->description = $item['description'];
        $this->assigned_to = $item['assigned_to'] ? (int) $item['assigned_to'] : null;
        $this->priority = $item['priority'];
        $this->status = $item['status'];
        $this->due_at = $item['due_at'];
        $this->estimated_minutes = $item['estimated_minutes'];
        $this->spent_minutes = $item['spent_minutes'];
    }

    public function save()
    {
        abort_unless(auth()->user()?->can($this->taskId ? 'tasks.update' : 'tasks.create'), 403);

        /** @var User $user */
        $user = auth()->user();
        $scope = $this->scopeBuilder->for($user);
        $data = $this->validate();

        $this->assertProjectAllowed((int) $data['project_id'], $scope);
        $this->assertAssigneeAllowed($data['assigned_to'] ?? null);

        $taskId = $this->saveTask->execute(
            $this->taskId,
            [
                'project_id' => (int) $data['project_id'],
                'title' => $data['title'],
                'description' => $data['description'] ?: null,
                'assigned_to' => $data['assigned_to'] ?: null,
                'priority' => $data['priority'],
                'status' => $data['status'],
                'due_at' => $data['due_at'] ?: null,
                'estimated_minutes' => $data['estimated_minutes'],
                'spent_minutes' => $data['spent_minutes'],
                ...($this->taskId ? [] : ['created_by' => $user->id]),
            ],
            $this->attachments,
        );

        session()->flash('success', $this->taskId ? __('app.updated_successfully') : __('app.created_successfully'));

        return $this->redirectRoute('tasks.show', ['task' => $taskId], navigate: true);
    }

    protected function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')->whereNull('deleted_at')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'status' => ['required', Rule::enum(TaskStatus::class)],
            'due_at' => ['nullable', 'date'],
            'estimated_minutes' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'spent_minutes' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'attachments' => ['array', 'max:10'],
            'attachments.*' => ['file', 'max:10240'],
        ];
    }

    private function assertProjectAllowed(int $projectId, array $scope): void
    {
        if ($scope['manage_all']) {
            return;
        }

        abort_unless(
            DB::table('project_user')->where('project_id', $projectId)->where('user_id', $scope['actor_id'])->exists(),
            403,
        );
    }

    private function assertAssigneeAllowed(?int $userId): void
    {
        if (! $userId) {
            return;
        }

        $user = User::query()->findOrFail($userId);
        abort_if(! $user->is_active, 422);
    }

    public function render()
    {
        /** @var User $user */
        $user = auth()->user();
        $scope = $this->scopeBuilder->for($user);

        return view('tasks::form', [
            'options' => $this->formOptions->get($user, $scope),
            'statuses' => TaskStatus::cases(),
            'priorities' => TaskPriority::cases(),
        ])->title($this->taskId ? __('tasks::messages.edit_task') : __('tasks::messages.new_task'));
    }
}
