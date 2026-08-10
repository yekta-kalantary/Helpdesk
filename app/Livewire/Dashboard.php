<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Tasks\Infrastructure\Models\Task;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();

        $projects = Project::query();
        $tasks = Task::query()->with('project');

        if (! $user->is_admin) {
            $projects->whereHas('members', fn ($members) => $members->whereKey($user->id));
            $tasks->whereHas('project.members', fn ($members) => $members->whereKey($user->id));
        }

        $projectCount = (clone $projects)->count();
        $taskCount = (clone $tasks)->count();
        $completedTaskCount = (clone $tasks)->where('is_done', true)->count();

        return view('livewire.dashboard', [
            'projectCount' => $projectCount,
            'taskCount' => $taskCount,
            'openTaskCount' => $taskCount - $completedTaskCount,
            'completedTaskCount' => $completedTaskCount,
            'userCount' => $user->is_admin
                ? User::query()->where('is_admin', false)->count()
                : null,
            'recentProjects' => (clone $projects)->latest('id')->limit(5)->get(),
            'recentTasks' => (clone $tasks)->latest('id')->limit(5)->get(),
        ])->title(__('app.dashboard'));
    }
}
