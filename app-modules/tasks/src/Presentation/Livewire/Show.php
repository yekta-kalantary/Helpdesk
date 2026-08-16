<?php

namespace Modules\Tasks\Presentation\Livewire;

use App\Models\Activity;
use DomainException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\ProjectTaskStatus;
use Modules\Tasks\Application\TaskChecklist;
use Modules\Tasks\Application\TaskCollaboration;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Infrastructure\Models\Attachment;
use Modules\Tasks\Infrastructure\Models\Task;
use Modules\Tasks\Infrastructure\Models\TaskChecklistItem;
use Modules\Tasks\Infrastructure\Models\TaskComment;

class Show extends Component
{
    use WithFileUploads;
    use WithPagination;

    #[Locked]
    public int $taskId;

    public string $comment = '';

    public array $uploads = [];

    public string $checklistTitle = '';

    public array $checklistEdits = [];

    public function mount(string $task): mixed
    {
        /** @var User $user */
        $user = auth()->user();
        $item = ctype_digit($task)
            ? Task::query()->visibleTo($user)->findOrFail((int) $task)
            : Task::query()->visibleTo($user)->where('reference', $task)->firstOrFail();

        if (ctype_digit($task)) {
            return redirect()->route('tasks.show', $item);
        }

        $this->taskId = $item->id;
        $this->checklistEdits = $item->checklistItems()->pluck('title', 'id')->mapWithKeys(fn ($title, $id): array => [(string) $id => $title])->all();

        return null;
    }

    public function changeStatus(int $status, TaskWorkflow $workflow): void
    {
        /** @var User $user */
        $user = auth()->user();
        $task = Task::query()->visibleTo($user)->findOrFail($this->taskId);
        $target = ProjectTaskStatus::query()->where('project_id', $task->project_id)->active()->findOrFail($status);

        try {
            $workflow->changeStatus($user, $task, $target);
            session()->flash('success', 'وضعیت تسک تغییر کرد.');
        } catch (DomainException $e) {
            $this->addError('status', $e->getMessage());
        }
    }

    public function addSubtask(TaskChecklist $checklist): void
    {
        /** @var User $user */
        $user = auth()->user();
        $this->validate(['checklistTitle' => ['required', 'string', 'max:255']]);
        $task = Task::query()->visibleTo($user)->findOrFail($this->taskId);

        try {
            $item = $checklist->add($user, $task, $this->checklistTitle);
            $this->checklistEdits[(string) $item->id] = $item->title;
            $this->checklistTitle = '';
        } catch (DomainException $e) {
            $this->addError('checklistTitle', $e->getMessage());
        }
    }

    public function toggleSubtask(int $item, bool $completed, TaskChecklist $checklist): void
    {
        /** @var User $user */
        $user = auth()->user();
        $record = TaskChecklistItem::query()->where('task_id', $this->taskId)->whereNull('removed_at')->findOrFail($item);

        try {
            $checklist->toggle($user, $record, $completed);
        } catch (DomainException $e) {
            $this->addError('checklist', $e->getMessage());
        }
    }

    public function renameSubtask(int $item, TaskChecklist $checklist): void
    {
        /** @var User $user */
        $user = auth()->user();
        $record = TaskChecklistItem::query()->where('task_id', $this->taskId)->whereNull('removed_at')->findOrFail($item);
        $title = (string) ($this->checklistEdits[(string) $item] ?? '');

        try {
            $record = $checklist->rename($user, $record, $title);
            $this->checklistEdits[(string) $item] = $record->title;
        } catch (DomainException $e) {
            $this->addError('checklistEdits.'.$item, $e->getMessage());
        }
    }

    public function removeSubtask(int $item, TaskChecklist $checklist): void
    {
        /** @var User $user */
        $user = auth()->user();
        $record = TaskChecklistItem::query()->where('task_id', $this->taskId)->whereNull('removed_at')->findOrFail($item);

        try {
            $checklist->remove($user, $record);
            unset($this->checklistEdits[(string) $item]);
        } catch (DomainException $e) {
            $this->addError('checklist', $e->getMessage());
        }
    }

    public function moveSubtask(int $item, string $direction, TaskChecklist $checklist): void
    {
        /** @var User $user */
        $user = auth()->user();
        $task = Task::query()->visibleTo($user)->findOrFail($this->taskId);
        $ids = $task->checklistItems()->pluck('id')->all();
        $index = array_search($item, $ids, true);

        if ($index === false) {
            return;
        }

        $target = $direction === 'up' ? $index - 1 : $index + 1;
        if (! isset($ids[$target])) {
            return;
        }

        [$ids[$index], $ids[$target]] = [$ids[$target], $ids[$index]];

        try {
            $checklist->reorder($user, $task, $ids);
        } catch (DomainException $e) {
            $this->addError('checklist', $e->getMessage());
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
                'projectStatus:id,project_id,title,is_done,is_active,position',
                'workGroup:id,title',
                'creator:id,name,last_name',
                'assignee:id,name,last_name',
                'checklistItems:id,task_id,title,is_completed,position,created_by,removed_at',
            ])
            ->findOrFail($this->taskId);

        foreach ($task->checklistItems as $item) {
            $this->checklistEdits[(string) $item->id] ??= $item->title;
        }

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
            ->paginate(20, ['*'], 'commentsPage');

        $taskAttachments = Attachment::query()
            ->where('task_id', $task->id)
            ->whereNull('comment_id')
            ->when(! $user->isAdmin(), fn ($query) => $query->whereNull('hidden_at'))
            ->orderBy('id')
            ->paginate(20, ['*'], 'attachmentsPage');

        $activities = Activity::query()
            ->where('task_id', $task->id)
            ->when(! $user->isAdmin(), fn ($query) => $query->withoutModeration())
            ->with('actor:id,name,last_name')
            ->latest('id')
            ->paginate(50, ['*'], 'taskActivitiesPage');

        $canChangeStatus = $task->project->isActive() && $task->project->client->isActive();
        $canCollaborate = $canChangeStatus && ! $task->isDone();
        $activeStatuses = $task->project->taskStatuses()->active()->orderBy('position')->get(['id', 'title', 'is_done', 'position']);

        return view('tasks::show', [
            'task' => $task,
            'comments' => $comments,
            'taskAttachments' => $taskAttachments,
            'activities' => $activities,
            'canCollaborate' => $canCollaborate,
            'canChangeStatus' => $canChangeStatus,
            'canEditTask' => $user->isAdmin() && $canCollaborate,
            'activeStatuses' => $activeStatuses,
            'checklistCompleted' => $task->checklistItems->where('is_completed', true)->count(),
            'isAdmin' => $user->isAdmin(),
        ])->title($task->reference.' · '.$task->title);
    }
}
