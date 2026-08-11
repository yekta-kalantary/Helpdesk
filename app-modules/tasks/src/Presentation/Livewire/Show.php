<?php

namespace Modules\Tasks\Presentation\Livewire;

use App\Models\Activity;
use DomainException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Tasks\Application\TaskCollaboration;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskStatus;
use Modules\Tasks\Infrastructure\Models\Attachment;
use Modules\Tasks\Infrastructure\Models\Task;
use Modules\Tasks\Infrastructure\Models\TaskComment;

class Show extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $taskId;

    public string $comment = '';

    public array $uploads = [];

    public function mount(int $task): void
    {
        /** @var User $user */
        $user = auth()->user();
        $this->taskId = Task::query()->visibleTo($user)->findOrFail($task)->id;
    }

    public function transition(string $status, TaskWorkflow $workflow): void
    {
        /** @var User $user */
        $user = auth()->user();
        abort_if($user->isAdmin(), 403);

        $task = Task::query()->visibleTo($user)->findOrFail($this->taskId);

        try {
            $workflow->transitionByCustomer($user, $task, TaskStatus::from($status));
            session()->flash('success', 'وضعیت تسک تغییر کرد.');
        } catch (DomainException) {
            abort(403);
        }
    }

    public function addComment(TaskCollaboration $collaboration): void
    {
        /** @var User $user */
        $user = auth()->user();
        $task = Task::query()->visibleTo($user)->findOrFail($this->taskId);

        try {
            $collaboration->comment($user, $task, $this->comment, $this->uploads);
        } catch (DomainException) {
            $this->addError('comment', 'این تسک یا پروژه برای همکاری بسته است.');

            return;
        }

        $this->reset(['comment', 'uploads']);
        session()->flash('success', 'نظر ثبت شد.');
    }

    public function hideComment(int $comment, TaskCollaboration $collaboration): void
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->isAdmin(), 403);

        $item = TaskComment::query()->where('task_id', $this->taskId)->findOrFail($comment);
        $collaboration->hideComment($user, $item);
    }

    public function hideAttachment(int $attachment, TaskCollaboration $collaboration): void
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->isAdmin(), 403);

        $item = Attachment::query()->where('task_id', $this->taskId)->findOrFail($attachment);
        $collaboration->hideAttachment($user, $item);
    }

    public function render()
    {
        /** @var User $user */
        $user = auth()->user();

        $task = Task::query()
            ->visibleTo($user)
            ->with([
                'project.client:id,name,status',
                'creator:id,name,last_name',
                'assignee:id,name,last_name',
            ])
            ->findOrFail($this->taskId);

        $comments = TaskComment::query()
            ->where('task_id', $task->id)
            ->when(! $user->isAdmin(), fn ($query) => $query->whereNull('hidden_at'))
            ->with([
                'user:id,name,last_name',
                'attachments' => fn ($query) => $query
                    ->when(! $user->isAdmin(), fn ($attachments) => $attachments->whereNull('hidden_at'))
                    ->orderBy('id'),
            ])
            ->orderBy('id')
            ->get();

        $taskAttachments = Attachment::query()
            ->where('task_id', $task->id)
            ->whereNull('comment_id')
            ->when(! $user->isAdmin(), fn ($query) => $query->whereNull('hidden_at'))
            ->orderBy('id')
            ->get();

        $activities = Activity::query()
            ->where('task_id', $task->id)
            ->with('actor:id,name,last_name')
            ->latest('id')
            ->limit(50)
            ->get();

        $canCollaborate = $task->project->isActive()
            && $task->project->client->isActive()
            && ! $task->isTerminal();

        $customerTransitions = [];
        if (! $user->isAdmin() && $canCollaborate && $task->assigned_to === $user->id) {
            $customerTransitions = [
                TaskStatus::Todo,
                TaskStatus::InProgress,
                TaskStatus::WaitingAdmin,
                TaskStatus::Completed,
            ];
        }

        return view('tasks::show', [
            'task' => $task,
            'comments' => $comments,
            'taskAttachments' => $taskAttachments,
            'activities' => $activities,
            'canCollaborate' => $canCollaborate,
            'customerTransitions' => $customerTransitions,
            'isAdmin' => $user->isAdmin(),
        ])->title($task->reference.' · '.$task->title);
    }
}
