<?php

namespace Modules\Projects\Presentation\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
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
        abort_unless(auth()->user()?->can('projects.delete'), 403);
        $this->projects->delete($project);
        session()->flash('success', __('app.deleted_successfully'));
    }

    public function render()
    {
        return view('projects::index', [
            'projects' => $this->projects->search(trim($this->q) ?: null),
        ])->title(__('projects::messages.projects'));
    }
}
