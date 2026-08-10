<?php

namespace Modules\Tasks\Presentation\Livewire;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Tasks\Application\Queries\TaskAccessScope;
use Modules\Tasks\Domain\Contracts\TaskRepository;

class Form extends Component
{
    #[Locked]
    public ?int $taskId = null;

    public ?int $project_id = null;

    public string $title = '';

    public ?string $description = null;

    public bool $is_done = false;

    protected TaskRepository $tasks;

    protected TaskAccessScope $scopeBuilder;

    public function boot(TaskRepository $tasks, TaskAccessScope $scopeBuilder): void
    {
        $this->tasks = $tasks;
        $this->scopeBuilder = $scopeBuilder;
    }

    public function mount(?int $task = null): void
    {
        abort_unless(auth()->user()?->is_admin, 403);

        if (! $task) {
            $project = request()->integer('project');
            $this->project_id = $project ?: null;

            return;
        }

        $item = $this->tasks->findAccessible($task, $this->scopeBuilder->for(auth()->user()));
        $this->taskId = $task;
        $this->project_id = (int) $item['project_id'];
        $this->title = $item['title'];
        $this->description = $item['description'];
        $this->is_done = $item['is_done'];
    }

    public function save()
    {
        abort_unless(auth()->user()?->is_admin, 403);

        $data = $this->validate();
        $attributes = [
            'project_id' => (int) $data['project_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
            'is_done' => $data['is_done'],
        ];

        if ($this->taskId) {
            $this->tasks->update($this->taskId, $attributes);
        } else {
            $this->tasks->create($attributes);
        }

        session()->flash('success', $this->taskId ? __('app.updated_successfully') : __('app.created_successfully'));

        return $this->redirectRoute('tasks.index', ['project' => $attributes['project_id']], navigate: true);
    }

    protected function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'is_done' => ['boolean'],
        ];
    }

    public function render()
    {
        return view('tasks::form', [
            'projects' => Project::query()->orderBy('title')->get(['id', 'title']),
        ])->title($this->taskId ? __('tasks::messages.edit_task') : __('tasks::messages.new_task'));
    }
}
