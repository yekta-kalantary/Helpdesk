<?php

namespace Modules\Contacts\Presentation\Livewire;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\Contacts\Domain\Contracts\ContactRepository;
use Spatie\Permission\Models\Role;

class Form extends Component
{
    #[Locked]
    public ?int $contactId = null;

    #[Locked]
    public ?int $userId = null;

    #[Url(except: 'general')]
    public string $tab = 'general';

    public string $first_name = '';
    public string $last_name = '';
    public ?string $gender = null;
    public string $email = '';
    public string $mobile = '';
    public ?string $province = null;
    public ?string $city = null;
    public ?string $address = null;
    public ?string $postal_code = null;

    public bool $account_enabled = false;
    public ?string $role = null;
    public string $password = '';
    public string $password_confirmation = '';

    protected ContactRepository $contacts;

    public function boot(ContactRepository $contacts): void
    {
        $this->contacts = $contacts;
    }

    public function mount(?int $contact = null): void
    {
        abort_unless(in_array($this->tab, ['general', 'contact-info', 'account-settings'], true), 404);

        if (! $contact) {
            return;
        }

        $item = $this->contacts->find($contact);
        $this->contactId = $contact;
        $this->userId = $item['user_id'] ? (int) $item['user_id'] : null;
        $this->first_name = $item['first_name'];
        $this->last_name = $item['last_name'];
        $this->gender = $item['gender'];
        $this->email = $item['email'];
        $this->mobile = $item['mobile'];
        $this->province = $item['province'];
        $this->city = $item['city'];
        $this->address = $item['address'];
        $this->postal_code = $item['postal_code'];
        $this->account_enabled = (bool) $item['account_enabled'];
        $this->role = $item['role'];
    }

    public function setTab(string $tab): void
    {
        abort_unless(in_array($tab, ['general', 'contact-info', 'account-settings'], true), 404);
        $this->tab = $tab;
    }

    public function save()
    {
        abort_unless(auth()->user()?->can($this->contactId ? 'contacts.update' : 'contacts.create'), 403);

        $data = $this->validate();
        $contactId = $this->contacts->save(
            $this->contactId,
            [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'gender' => $data['gender'] ?: null,
                'email' => $data['email'],
                'mobile' => $data['mobile'],
                'province' => $data['province'] ?: null,
                'city' => $data['city'] ?: null,
                'address' => $data['address'] ?: null,
                'postal_code' => $data['postal_code'] ?: null,
            ],
            [
                'enabled' => $data['account_enabled'],
                'role' => $data['role'] ?? null,
                'password' => $data['password'] ?? null,
            ],
        );

        session()->flash('success', $this->contactId ? __('app.updated_successfully') : __('app.created_successfully'));

        return $this->redirectRoute('contacts.edit', ['contact' => $contactId, 'tab' => $this->tab], navigate: true);
    }

    protected function rules(): array
    {
        $email = Rule::unique('contacts', 'email');
        if ($this->contactId) {
            $email->ignore($this->contactId);
        }

        $roleRule = Rule::exists('roles', 'name')->where('guard_name', 'web');

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'email' => ['required', 'email', 'max:255', $email],
            'mobile' => ['required', 'string', 'max:32'],
            'province' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:5000'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'account_enabled' => ['boolean'],
            'role' => [Rule::requiredIf($this->account_enabled), 'nullable', 'string', $roleRule],
            'password' => [Rule::requiredIf($this->account_enabled && ! $this->userId), 'nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function render()
    {
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->when($this->role !== 'admin', fn ($query) => $query->where('name', '!=', 'admin'))
            ->orderBy('name')
            ->pluck('name')
            ->all();

        return view('contacts::form', ['roles' => $roles])
            ->title($this->contactId ? __('contacts::messages.edit_contact') : __('contacts::messages.new_contact'));
    }
}
