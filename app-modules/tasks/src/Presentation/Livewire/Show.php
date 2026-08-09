<?php

namespace Modules\Tasks\Presentation\Livewire;

use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Modules\Tasks\Application\Queries\TaskAccessScope;
use Modules\Tasks\Domain\Contracts\TaskAttachmentStore;
use Modules\Tasks\Domain\Contracts\TaskRepository;
use Modules\Tasks\Domain\Enums\TaskStatus;

class Show extends Component
{
    #[Locked]
    public int $taskId;

    public string $commentBody = '';
    public string $status = 'todo';

    protected TaskRepository $tasks;
    protected TaskAccessScope $scopeBuilder;
    protected TaskAttachmentStore $attachments;

    public function boot(
        TaskRepository $tasks,
        TaskAccessScope $scopeBuilder,
        TaskAttachmentStore $attachments,
    ): void {
        $this->tasks = $tasks;
        $this->scopeBuilder = $scopeBuilder;
        $this->attachments = $attachments;
    }

    public function mount(int $task): void
    {
        $item = $this->tasks->findAccessible($task, $this->scope());
        $this->taskId = $task;
        $this->status = $item['status'];
    }

    public function addComment(): void
    {
        abort_unless(auth()->user()?->can('tasks.comment'), 403);
        $this->tasks->findAccessible($this->taskId, $this->scope());
        $this->validate(['commentBody' => ['required', 'string', 'max:5000']]);

        $this->tasks->addComment($this->taskId, (int) auth()->id(), $this->commentBody);
        $this->reset('commentBody');
        session()->flash('success', __('app.created_successfully'));
    }

    public function updateStatus(): void
    {
        abort_unless(auth()->user()?->can('tasks.update'), 403);
        $this->tasks->findAccessible($this->taskId, $this->scope());
        $data = $this->validate(['status' => ['required', Rule::enum(TaskStatus::class)]]);
        $this->tasks->update($this->taskId, ['status' => $data['status']]);
        session()->flash('success', __('app.updated_successfully'));
    }

    public function deleteAttachment(int $media): void
    {
        abort_unless(auth()->user()?->can('tasks.update'), 403);
        $this->tasks->findAccessible($this->taskId, $this->scope());
        $this->attachments->delete($this->taskId, $media);
        session()->flash('success', __('app.deleted_successfully'));
    }

    public function deleteTask()
    {
        abort_unless(auth()->user()?->can('tasks.delete'), 403);
        $this->tasks->findAccessible($this->taskId, $this->scope());
        $this->tasks->delete($this->taskId);
        session()->flash('success', __('app.deleted_successfully'));

        return $this->redirectRoute('tasks.index', navigate: true);
    }

    private function scope(): array
    {
        /** @var User $user */
        $user = auth()->user();

        return $this->scopeBuilder->for($user);
    }

    public function render()
    {
        $task = $this->tasks->findAccessible($this->taskId, $this->scope());

        return view('tasks::show', [
            'task' => $task,
            'statuses' => TaskStatus::cases(),
        ])->title($task['title']);
    }
}
