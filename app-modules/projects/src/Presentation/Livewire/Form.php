<?php

namespace Modules\Projects\Presentation\Livewire;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\Actions\SaveProject;
use Modules\Projects\Application\Queries\ProjectFormOptions;
use Modules\Projects\Domain\Contracts\ProjectRepository;
use Modules\Projects\Domain\Enums\ProjectCategory;
use Modules\Projects\Domain\Enums\ProjectStatus;
use Modules\Projects\Domain\Enums\ProjectType;

class Form extends Component
{
    #[Locked]
    public ?int $projectId = null;

    public string $category = 'contact';

    public ?int $contact_id = null;

    public string $contactSearch = '';

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
        $this->category = $item['category'];
        $this->contact_id = $item['contact_id'] ? (int) $item['contact_id'] : null;
        $this->contactSearch = (string) ($item['contact_name'] ?? '');
        $this->title = $item['title'];
        $this->type = $item['type'];
        $this->status = $item['status'];
        $this->description = $item['description'];
        $this->starts_at = $item['starts_at'];
        $this->ends_at = $item['ends_at'];
        $this->member_ids = array_map('intval', $item['member_ids'] ?? []);
    }

    public function updatedCategory(string $category): void
    {
        if ($category === ProjectCategory::Internal->value) {
            $this->contact_id = null;
            $this->contactSearch = '';
        }
    }

    public function updatedContactSearch(): void
    {
        if ($this->contact_id === null) {
            return;
        }

        $this->contact_id = null;
        $this->resetValidation('contact_id');
    }

    public function selectContact(int $contactId): void
    {
        $contact = $this->options->findContact($contactId);

        if (! $contact) {
            $this->contact_id = null;
            $this->addError('contact_id', __('validation.exists', ['attribute' => __('projects::messages.contact')]));

            return;
        }

        $this->contact_id = $contact['id'];
        $this->contactSearch = $contact['name'];
        $this->resetValidation('contact_id');
    }

    public function save()
    {
        abort_unless(auth()->user()?->can($this->projectId ? 'projects.update' : 'projects.create'), 403);

        $data = $this->validate();
        $members = User::query()
            ->whereIn('id', $data['member_ids'] ?? [])
            ->where('is_active', true)
            ->pluck('id')
            ->all();

        $this->saveProject->execute(
            $this->projectId,
            [
                'category' => $data['category'],
                'contact_id' => $data['category'] === ProjectCategory::Contact->value
                    ? (int) $data['contact_id']
                    : null,
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
            'category' => ['required', Rule::enum(ProjectCategory::class)],
            'contact_id' => [
                Rule::requiredIf($this->category === ProjectCategory::Contact->value),
                Rule::prohibitedIf($this->category === ProjectCategory::Internal->value),
                'nullable',
                'integer',
                Rule::exists('contacts', 'id'),
            ],
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
            'options' => $this->options->get(trim($this->contactSearch) ?: null),
            'categories' => ProjectCategory::cases(),
            'statuses' => ProjectStatus::cases(),
            'types' => ProjectType::cases(),
        ])->title($this->projectId ? __('projects::messages.edit_project') : __('projects::messages.new_project'));
    }
}
