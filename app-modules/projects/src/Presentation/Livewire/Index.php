<?php

namespace Modules\Projects\Presentation\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;

class Index extends Component
{
    #[Url(as: 'q', except: '')]
    public string $q = '';

    public function delete(int $project): void
    {
        abort_unless(auth()->user()?->is_admin, 403);

        Project::query()->findOrFail($project)->delete();

        session()->flash('success', __('app.deleted_successfully'));
    }

    public function render()
    {
        /** @var User $user */
        $user = auth()->user();
        $term = trim($this->q);

        $projects = Project::query()
            ->when(! $user->is_admin, fn ($query) => $query->whereHas(
                'members',
                fn ($members) => $members->whereKey($user->id),
            ))
            ->when($term !== '', fn ($query) => $query->where('title', 'like', "%{$term}%"))
            ->withCount('members')
            ->orderByDesc('id')
            ->get();

        return view('projects::index', [
            'projects' => $projects,
            'isAdmin' => $user->is_admin,
        ])->title(__('projects::messages.projects'));
    }
}
