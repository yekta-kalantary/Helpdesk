<?php

namespace Modules\Projects\Presentation\Livewire;

use Livewire\Attributes\Locked;
use Livewire\Component;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\Actions\SaveProject;
use Modules\Projects\Application\Queries\ProjectFormOptions;
use Modules\Projects\Domain\Contracts\ProjectRepository;

class Form extends Component
{
    #[Locked]
    public ?int $projectId = null;

    public string $title = '';

    public ?string $description = null;

    public array $member_ids = [];

    protected ProjectRepository $projects;

    protected SaveProject $saveProject;

    protected ProjectFormOptions $options;

    public function boot(ProjectRepository $projects, SaveProject $saveProject, ProjectFormOptions $options): void
    {
        $this->projects = $projects;
        $this->saveProject = $saveProject;
        $this->options = $options;
    }

    public function mount(?int $project = null): void
    {
        abort_unless(auth()->user()?->is_admin, 403);

        if (! $project) {
            return;
        }

        $item = $this->projects->find($project);
        $this->projectId = $project;
        $this->title = $item['title'];
        $this->description = $item['description'];
        $this->member_ids = array_map('intval', $item['member_ids'] ?? []);
    }

    public function save()
    {
        abort_unless(auth()->user()?->is_admin, 403);

        $data = $this->validate();
        $members = User::query()
            ->where('is_admin', false)
            ->where('is_active', true)
            ->whereIn('id', $data['member_ids'] ?? [])
            ->pluck('id')
            ->all();

        $this->saveProject->execute(
            $this->projectId,
            [
                'title' => $data['title'],
                'description' => $data['description'] ?: null,
            ],
            $members,
        );

        session()->flash('success', $this->projectId ? __('app.updated_successfully') : __('app.created_successfully'));

        return $this->redirectRoute('projects.index', navigate: true);
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'member_ids' => ['array'],
            'member_ids.*' => ['integer', 'exists:users,id'],
        ];
    }

    public function render()
    {
        return view('projects::form', [
            'members' => $this->options->members(),
        ])->title($this->projectId ? __('projects::messages.edit_project') : __('projects::messages.new_project'));
    }
}
