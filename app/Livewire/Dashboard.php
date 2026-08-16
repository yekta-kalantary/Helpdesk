<?php

namespace App\Livewire;

use App\Models\Activity;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Domain\Enums\ProjectStatus;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Tasks\Infrastructure\Models\Task;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        /** @var User $user */
        $user = auth()->user();

        $projects = Project::query()->visibleTo($user);
        $tasks = Task::query()->visibleTo($user);
        $openTasks = fn () => (clone $tasks)->whereHas(
            'projectStatus',
            fn ($statuses) => $statuses->where('is_done', false),
        );

        $activities = Activity::query()
            ->when(! $user->isAdmin(), fn ($query) => $query
                ->whereHas('project', fn ($projectQuery) => $projectQuery->visibleTo($user))
                ->withoutModeration());

        return view('livewire.dashboard', [
            'isAdmin' => $user->isAdmin(),
            'activeClientCount' => $user->isAdmin() ? Client::query()->active()->count() : null,
            'activeProjectCount' => (clone $projects)->where('status', ProjectStatus::Active->value)->count(),
            'openTaskCount' => $openTasks()->count(),
            'unassignedOpenTaskCount' => $user->isAdmin() ? $openTasks()->whereNull('assigned_to')->count() : null,
            'assignedToMeCount' => $openTasks()->where('assigned_to', $user->id)->count(),
            'overdueCount' => (clone $tasks)->overdue()->count(),
            'recentProjects' => (clone $projects)->latest('updated_at')->limit(5)->get(['id', 'client_id', 'name', 'description', 'status', 'updated_at']),
            'recentTasks' => (clone $tasks)
                ->with(['project:id,name', 'projectStatus:id,title,is_done'])
                ->latest('updated_at')
                ->limit(8)
                ->get(),
            'recentActivities' => $activities->with('actor:id,name,last_name')->latest('id')->limit(10)->get(),
        ])->title(__('app.dashboard'));
    }
}
