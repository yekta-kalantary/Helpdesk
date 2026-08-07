<?php

namespace Modules\Projects\Presentation\Livewire;

use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Modules\Projects\Application\Actions\SaveProject;
use Modules\Projects\Application\Queries\ProjectFormOptions;
use Modules\Projects\Domain\Contracts\ProjectRepository;
use Modules\Projects\Domain\Enums\ProjectStatus;
use Modules\Projects\Domain\Enums\ProjectType;

class Form extends Component
{
    #[Locked]
    public ?int $projectId = null;

    public ?int $customer_id = null;

    public string $title = '';

    public string $type = 'website_design';

    public string $status = 'planning';

    public ?string $description = null;

    public ?string $starts_at = null;

    public ?string $ends_at = null;

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
        if (! $project) {
            return;
        }

        $item = $this->projects->find($project);
        $this->projectId = $project;
        $this->customer_id = (int) $item['customer_id'];
        $this->title = $item['title'];
        $this->type = $item['type'];
        $this->status = $item['status'];
        $this->description = $item['description'];
        $this->starts_at = $item['starts_at'];
        $this->ends_at = $item['ends_at'];
        $this->member_ids = array_map('intval', $item['member_ids'] ?? []);
    }

    public function save()
    {
        abort_unless(auth()->user()?->can($this->projectId ? 'projects.update' : 'projects.create'), 403);

        $data = $this->validate();
        $members = User::query()
            ->whereIn('id', $data['member_ids'] ?? [])
            ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'customer'))
            ->pluck('id')
            ->all();

        $this->saveProject->execute(
            $this->projectId,
            [
                'customer_id' => (int) $data['customer_id'],
                'title' => $data['title'],
                'type' => $data['type'],
                'status' => $data['status'],
                'description' => $data['description'] ?: null,
                'starts_at' => $data['starts_at'] ?: null,
                'ends_at' => $data['ends_at'] ?: null,
            ],
            $members,
        );

        session()->flash('success', $this->projectId ? __('app.updated_successfully') : __('app.created_successfully'));

        return $this->redirectRoute('projects.index', navigate: true);
    }

    protected function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')->whereNull('deleted_at')],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(ProjectType::class)],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'description' => ['nullable', 'string', 'max:10000'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'member_ids' => ['array'],
            'member_ids.*' => ['integer', 'exists:users,id'],
        ];
    }

    public function render()
    {
        return view('projects::form', [
            'options' => $this->options->get(),
            'statuses' => ProjectStatus::cases(),
            'types' => ProjectType::cases(),
        ])->title($this->projectId ? __('projects::messages.edit_project') : __('projects::messages.new_project'));
    }
}
