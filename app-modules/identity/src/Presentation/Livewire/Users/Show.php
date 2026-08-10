<?php

namespace Modules\Identity\Presentation\Livewire\Users;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\Identity\Infrastructure\Models\User;

class Show extends Component
{
    #[Locked]
    public int $userId;

    #[Url(as: 'tab', except: 'overview')]
    public string $tab = 'overview';

    public string $name = '';

    public string $last_name = '';

    public string $email = '';

    public ?string $mobile = null;

    public string $password = '';

    public string $password_confirmation = '';

    public bool $is_active = false;

    public function mount(int $user): void
    {
        abort_unless(auth()->user()?->is_admin, 403);

        $this->userId = User::query()
            ->where('is_admin', false)
            ->findOrFail($user)
            ->id;

        if (request()->routeIs('users.edit') && $this->tab === 'overview') {
            $this->tab = 'general';
        }

        $this->fillFromUser();
    }

    public function selectTab(string $tab): void
    {
        abort_unless(in_array($tab, ['overview', 'general', 'contact', 'account', 'projects'], true), 404);

        $this->resetValidation();
        $this->tab = $tab;
    }

    public function saveGeneral(): void
    {
        abort_unless(auth()->user()?->is_admin, 403);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
        ]);

        $this->user()->update($data);

        session()->flash('success', __('identity::messages.general_saved'));
    }

    public function saveContact(): void
    {
        abort_unless(auth()->user()?->is_admin, 403);

        $data = $this->validate([
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->userId)],
            'mobile' => ['nullable', 'string', 'max:32'],
        ]);

        $this->user()->update([
            'email' => $data['email'] ?: null,
            'mobile' => $data['mobile'] ?: null,
        ]);

        session()->flash('success', __('identity::messages.contact_saved'));
    }

    public function saveAccount(): void
    {
        abort_unless(auth()->user()?->is_admin, 403);

        $data = $this->validate([
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['boolean'],
        ]);

        $user = $this->user();

        if ($data['is_active'] && blank($user->email)) {
            $this->addError('is_active', __('identity::messages.email_required_to_activate'));

            return;
        }

        if ($data['is_active'] && blank($user->getRawOriginal('password')) && $data['password'] === '') {
            $this->addError('password', __('identity::messages.password_required_to_activate'));

            return;
        }

        $attributes = [
            'is_active' => $data['is_active'],
        ];

        if ($data['password'] !== '') {
            $attributes['password'] = $data['password'];
        }

        $user->update($attributes);

        $this->password = '';
        $this->password_confirmation = '';

        session()->flash('success', __('identity::messages.account_saved'));
    }

    private function fillFromUser(): void
    {
        $user = $this->user();

        $this->name = $user->name;
        $this->last_name = $user->last_name;
        $this->email = $user->email ?? '';
        $this->mobile = $user->mobile;
        $this->is_active = $user->is_active;
    }

    private function user(): User
    {
        return User::query()
            ->where('is_admin', false)
            ->findOrFail($this->userId);
    }

    public function render()
    {
        $user = $this->user();

        $projects = DB::table('projects')
            ->join('project_user', 'projects.id', '=', 'project_user.project_id')
            ->where('project_user.user_id', $this->userId)
            ->select('projects.id', 'projects.title', 'projects.description')
            ->orderBy('projects.title')
            ->get();

        $projectIds = $projects->pluck('id');
        $taskCount = DB::table('tasks')->whereIn('project_id', $projectIds)->count();
        $openTaskCount = DB::table('tasks')
            ->whereIn('project_id', $projectIds)
            ->where('is_done', false)
            ->count();
        $doneTaskCount = $taskCount - $openTaskCount;

        $projectTaskCounts = DB::table('tasks')
            ->whereIn('project_id', $projectIds)
            ->selectRaw('project_id, count(*) as total')
            ->groupBy('project_id')
            ->pluck('total', 'project_id');

        $hasPassword = filled($user->getRawOriginal('password'));

        return view('identity::users.show', compact(
            'user',
            'projects',
            'projectTaskCounts',
            'taskCount',
            'openTaskCount',
            'doneTaskCount',
            'hasPassword',
        ))->title($user->full_name);
    }
}
