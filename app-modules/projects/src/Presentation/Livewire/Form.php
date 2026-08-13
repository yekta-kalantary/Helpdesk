<?php

namespace Modules\Projects\Presentation\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Projects\Domain\Enums\ProjectStatus;
use Modules\Projects\Infrastructure\Models\Project;

class Form extends Component
{
    #[Locked]
    public ?int $projectId = null;

    public ?int $client_id = null;

    public string $name = '';

    public ?string $description = null;

    public ?string $start_date = null;

    public ?string $due_date = null;

    /** @var array<int, int> */
    public array $member_ids = [];

    public function mount(?int $project = null): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        if (! $project) {
            $this->client_id = request()->integer('client') ?: null;

            return;
        }

        $item = Project::query()->findOrFail($project);
        $this->projectId = $item->id;
        $this->client_id = $item->client_id;
        $this->name = $item->name;
        $this->description = $item->description;
        $this->start_date = $item->start_date?->toDateString();
        $this->due_date = $item->due_date?->toDateString();
        $this->member_ids = $item->activeMembers()
            ->pluck('users.id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    public function updatedClientId(): void
    {
        if (! $this->projectId) {
            $this->member_ids = [];
        }
    }

    public function save(ProjectMembershipManager $memberships)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $data = $this->validate([
            'client_id' => [
                'required',
                'integer',
                Rule::exists('clients', 'id')->where(fn ($query) => $query->where('status', 'active')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'member_ids' => ['array'],
            'member_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);

        /** @var User $actor */
        $actor = auth()->user();

        $project = $this->projectId
            ? Project::query()->findOrFail($this->projectId)
            : new Project([
                'client_id' => (int) $data['client_id'],
                'status' => ProjectStatus::Active,
            ]);

        $selected = User::query()
            ->customers()
            ->where('client_id', $project->client_id)
            ->whereIn('id', $data['member_ids'] ?? [])
            ->where(function ($query) use ($project): void {
                $query->active();

                if ($project->exists) {
                    $query->orWhereExists(function ($membership) use ($project): void {
                        $membership->selectRaw('1')
                            ->from('project_user')
                            ->whereColumn('project_user.user_id', 'users.id')
                            ->where('project_user.project_id', $project->id)
                            ->whereNull('project_user.removed_at');
                    });
                }
            })
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $requested = collect($data['member_ids'] ?? [])->map(fn ($id): int => (int) $id)->sort()->values()->all();
        $eligible = collect($selected)->sort()->values()->all();

        if ($requested !== $eligible) {
            $this->addError('member_ids', 'همه اعضا باید کاربر فعال همان مشتری باشند.');

            return null;
        }

        $project = DB::transaction(function () use ($actor, $data, $memberships, $project, $selected): Project {
            $project->fill([
                'name' => trim($data['name']),
                'description' => filled($data['description']) ? trim($data['description']) : null,
                'start_date' => $data['start_date'] ?: null,
                'due_date' => $data['due_date'] ?: null,
            ])->save();

            $current = $project->activeMembers()
                ->pluck('users.id')
                ->map(fn ($id): int => (int) $id)
                ->all();

            foreach (array_diff($selected, $current) as $userId) {
                $memberships->add($project, User::query()->findOrFail($userId), $actor);
            }

            foreach (array_diff($current, $selected) as $userId) {
                $memberships->remove($project, User::query()->findOrFail($userId), $actor);
            }

            return $project;
        });

        session()->flash('success', $this->projectId ? __('app.updated_successfully') : __('app.created_successfully'));

        return $this->redirectRoute('projects.show', ['project' => $project->id], navigate: true);
    }

    public function render()
    {
        $clients = Client::query()->active()->orderBy('name')->get(['id', 'name']);
        $members = $this->client_id
            ? User::query()
                ->customers()
                ->where('client_id', $this->client_id)
                ->where(function ($query): void {
                    $query->active();

                    if ($this->projectId) {
                        $query->orWhereExists(function ($membership): void {
                            $membership->selectRaw('1')
                                ->from('project_user')
                                ->whereColumn('project_user.user_id', 'users.id')
                                ->where('project_user.project_id', $this->projectId)
                                ->whereNull('project_user.removed_at');
                        });
                    }
                })
                ->orderBy('name')
                ->orderBy('last_name')
                ->get(['id', 'name', 'last_name'])
            : collect();

        $clientName = $this->projectId
            ? Client::query()->whereKey($this->client_id)->value('name')
            : null;

        return view('projects::form', compact('clients', 'members', 'clientName'))
            ->title($this->projectId ? __('projects::messages.edit_project') : __('projects::messages.new_project'));
    }
}
