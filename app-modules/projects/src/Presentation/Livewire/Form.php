<?php

namespace Modules\Projects\Presentation\Livewire;

use Livewire\Attributes\Locked;
use Livewire\Component;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;

class Form extends Component
{
    #[Locked]
    public ?int $projectId = null;

    public string $title = '';

    public ?string $description = null;

    public array $member_ids = [];

    public function mount(?int $project = null): void
    {
        abort_unless(auth()->user()?->is_admin, 403);

        if (! $project) {
            return;
        }

        $item = Project::query()->findOrFail($project);

        $this->projectId = $item->id;
        $this->title = $item->title;
        $this->description = $item->description;
        $this->member_ids = $item->members()->pluck('users.id')->map(fn ($id) => (int) $id)->all();
    }

    public function save()
    {
        abort_unless(auth()->user()?->is_admin, 403);

        $data = $this->validate();
        $memberIds = User::query()
            ->where('is_admin', false)
            ->where('is_active', true)
            ->whereIn('id', $data['member_ids'] ?? [])
            ->pluck('id')
            ->all();

        $project = $this->projectId
            ? Project::query()->findOrFail($this->projectId)
            : new Project;

        $project->fill([
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
        ])->save();

        $project->members()->sync($memberIds);

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
        $members = User::query()
            ->where('is_admin', false)
            ->where('is_active', true)
            ->orderBy('name')
            ->orderBy('last_name')
            ->get();

        return view('projects::form', compact('members'))
            ->title($this->projectId ? __('projects::messages.edit_project') : __('projects::messages.new_project'));
    }
}
