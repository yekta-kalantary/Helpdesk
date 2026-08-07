<?php

namespace Modules\Projects\Presentation\Livewire;

use App\Enums\PersonType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
        /** @var User $user */
        $user = auth()->user();
        $customerId = $user->person?->type === PersonType::Customer
            ? DB::table('customers')->where('person_id', $user->person_id)->whereNull('deleted_at')->value('id')
            : null;

        return view('projects::index', [
            'projects' => $this->projects->search(trim($this->q) ?: null, $customerId ? (int) $customerId : null),
        ])->title(__('projects::messages.projects'));
    }
}
