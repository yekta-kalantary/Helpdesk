<?php

namespace Modules\Tasks\Presentation\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Tasks\Infrastructure\Models\Task;

class Index extends Component
{
    #[Url(as: 'q', except: '')]
    public string $q = '';

    #[Url(as: 'project', except: null)]
    public ?int $projectId = null;

    public function delete(int $task): void
    {
        abort_unless(auth()->user()?->is_admin, 403);

        Task::query()->findOrFail($task)->delete();

        session()->flash('success', __('app.deleted_successfully'));
    }

    public function render()
    {
        /** @var User $user */
        $user = auth()->user();
        $term = trim($this->q);

        $tasks = Task::query()
            ->with('project:id,title')
            ->when(! $user->is_admin, fn ($query) => $query->whereHas(
                'project.members',
                fn ($members) => $members->whereKey($user->id),
            ))
            ->when($this->projectId, fn ($query) => $query->where('project_id', $this->projectId))
            ->when($term !== '', fn ($query) => $query->where('title', 'like', "%{$term}%"))
            ->orderBy('is_done')
            ->orderByDesc('id')
            ->get();

        return view('tasks::index', [
            'tasks' => $tasks,
            'isAdmin' => $user->is_admin,
        ])->title(__('tasks::messages.tasks'));
    }
}
