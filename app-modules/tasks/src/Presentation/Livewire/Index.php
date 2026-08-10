<?php

namespace Modules\Tasks\Presentation\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Tasks\Application\Queries\TaskAccessScope;
use Modules\Tasks\Domain\Contracts\TaskRepository;
use Modules\Tasks\Domain\Enums\TaskStatus;

class Index extends Component
{
    #[Url(as: 'q', except: '')]
    public string $q = '';

    #[Url(as: 'project', except: null)]
    public ?int $projectId = null;

    protected TaskRepository $tasks;

    protected TaskAccessScope $scopeBuilder;

    public function boot(TaskRepository $tasks, TaskAccessScope $scopeBuilder): void
    {
        $this->tasks = $tasks;
        $this->scopeBuilder = $scopeBuilder;
    }

    public function render()
    {
        /** @var User $user */
        $user = auth()->user();
        $scope = $this->scopeBuilder->for($user);

        return view('tasks::index', [
            'tasks' => $this->tasks->search($scope, $this->projectId, trim($this->q) ?: null),
            'statuses' => TaskStatus::cases(),
        ])->title(__('tasks::messages.tasks'));
    }
}
