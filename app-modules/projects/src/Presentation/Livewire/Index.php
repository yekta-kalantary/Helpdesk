<?php

namespace Modules\Projects\Presentation\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Domain\Contracts\ProjectRepository;

class Index extends Component
{
    #[Url(as: 'q', except: '')]
    public string $q = '';

    protected ProjectRepository $projects;

    public function boot(ProjectRepository $projects): void
    {
        $this->projects = $projects;
    }

    public function delete(int $project): void
    {
        abort_unless(auth()->user()?->is_admin, 403);
        $this->projects->delete($project);
        session()->flash('success', __('app.deleted_successfully'));
    }

    public function render()
    {
        /** @var User $user */
        $user = auth()->user();

        return view('projects::index', [
            'projects' => $this->projects->search(trim($this->q) ?: null, $user->id, $user->is_admin),
            'isAdmin' => $user->is_admin,
        ])->title(__('projects::messages.projects'));
    }
}
