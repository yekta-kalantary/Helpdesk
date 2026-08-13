<?php

namespace Modules\Identity\Presentation\Livewire\Users;

use App\Support\CustomerAssignmentRequeuer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;
use Throwable;

class Show extends Component
{
    #[Locked]
    public int $userId;

    public string $name = '';

    public string $last_name = '';

    public string $email = '';

    public ?string $mobile = null;

    public string $password = '';

    public string $password_confirmation = '';

    public bool $is_active = false;

    public function mount(int $user): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $this->userId = User::query()->customers()->findOrFail($user)->id;
        $this->fillFromUser();
    }

    public function saveProfile(CustomerAssignmentRequeuer $assignments): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $this->email = Str::lower(trim($this->email));

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->userId)],
            'mobile' => ['nullable', 'string', 'max:32'],
            'is_active' => ['boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $this->user();
        $wasInactive = ! $user->is_active;
        $isDeactivating = $user->is_active && ! $data['is_active'];
        $attributes = [
            'name' => trim($data['name']),
            'last_name' => trim($data['last_name']),
            'email' => $data['email'],
            'mobile' => filled($data['mobile']) ? trim($data['mobile']) : null,
            'is_active' => $data['is_active'],
        ];

        if ($data['password'] !== '') {
            $attributes['password'] = $data['password'];
        }

        /** @var User $actor */
        $actor = auth()->user();

        DB::transaction(function () use ($actor, $assignments, $attributes, $isDeactivating, $user): void {
            if ($isDeactivating) {
                $assignments->requeue($user, $actor);
            }

            $user->update($attributes);
        });

        $this->password = '';
        $this->password_confirmation = '';

        if ($wasInactive && $user->is_active && blank($user->getRawOriginal('password'))) {
            $this->sendSetupLink();
        }

        session()->flash('success', __('app.updated_successfully'));
    }

    public function sendSetupLink(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $user = $this->user();

        try {
            Password::sendResetLink(['email' => $user->email]);
            session()->flash('success', 'لینک تنظیم رمز عبور ارسال شد.');
        } catch (Throwable $e) {
            Log::warning('Customer setup email delivery failed.', [
                'user_id' => $user->id,
                'exception' => $e::class,
            ]);
            session()->flash('success', 'کاربر ذخیره شد؛ ارسال ایمیل تنظیم رمز عبور ناموفق بود.');
        }
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
        return User::query()->customers()->with('client:id,name')->findOrFail($this->userId);
    }

    public function render()
    {
        $user = $this->user();
        $projects = Project::query()
            ->whereHas('members', fn ($members) => $members
                ->whereKey($user->id)
                ->whereNull('project_user.removed_at'))
            ->orderBy('name')
            ->get(['id', 'client_id', 'name', 'status']);

        return view('identity::users.show', compact('user', 'projects'))
            ->title($user->full_name);
    }
}
