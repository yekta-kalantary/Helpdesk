<?php

namespace Modules\Contacts\Presentation\Livewire;

use DomainException;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\Contacts\Domain\Contracts\ContactAccountGateway;
use Modules\Contacts\Domain\Contracts\ContactRepository;

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

    protected ContactAccountGateway $accounts;

    public function boot(ContactRepository $contacts, ContactAccountGateway $accounts): void
    {
        $this->contacts = $contacts;
        $this->accounts = $accounts;
    }

    public function mount(?int $contact = null): void
    {
        abort_unless(in_array($this->tab, ['general', 'contact-info', 'account-settings'], true), 404);

        if ($this->tab === 'account-settings') {
            abort_unless(auth()->user()?->can('users.view'), 403);
        }

        if (! $contact) {
            return;
        }

        $item = $this->contacts->find($contact);
        $this->contactId = $contact;
        $this->first_name = $item['first_name'];
        $this->last_name = $item['last_name'];
        $this->gender = $item['gender'];
        $this->email = $item['email'];
        $this->mobile = $item['mobile'];
        $this->province = $item['province'];
        $this->city = $item['city'];
        $this->address = $item['address'];
        $this->postal_code = $item['postal_code'];

        if (auth()->user()?->can('users.view')) {
            $account = $this->accounts->get($contact);
            $this->userId = $account['user_id'] ? (int) $account['user_id'] : null;
            $this->account_enabled = $account['account_enabled'];
            $this->role = $account['role'];
        }
    }

    public function setTab(string $tab): void
    {
        abort_unless(in_array($tab, ['general', 'contact-info', 'account-settings'], true), 404);

        if ($tab === 'account-settings') {
            abort_unless(auth()->user()?->can('users.view'), 403);
        }

        $this->tab = $tab;
    }

    public function save()
    {
        abort_unless(auth()->user()?->can($this->contactId ? 'contacts.update' : 'contacts.create'), 403);

        if ($this->tab === 'account-settings') {
            abort_unless(auth()->user()?->can('users.view'), 403);

            if ($this->userId) {
                abort_unless(auth()->user()?->can('users.update'), 403);
            } elseif ($this->account_enabled) {
                abort_unless(auth()->user()?->can('users.create'), 403);
            }
        }

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
        );

        if ($this->tab === 'account-settings' && ($this->userId || $data['account_enabled'])) {
            try {
                $this->accounts->save($contactId, [
                    'enabled' => $data['account_enabled'],
                    'role' => $data['role'] ?? null,
                    'password' => $data['password'] ?? null,
                ]);
            } catch (DomainException $exception) {
                $this->addError('role', __('contacts::messages.'.$exception->getMessage()));

                return null;
            }

            $account = $this->accounts->get($contactId);
            $this->userId = $account['user_id'] ? (int) $account['user_id'] : null;
            $this->account_enabled = $account['account_enabled'];
            $this->role = $account['role'];
        }

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
        $canViewAccounts = (bool) auth()->user()?->can('users.view');
        $roles = $this->tab === 'account-settings' && $canViewAccounts
            ? $this->accounts->assignableRoles($this->contactId)
            : [];

        return view('contacts::form', [
            'roles' => $roles,
            'canViewAccounts' => $canViewAccounts,
        ])->title($this->contactId ? __('contacts::messages.edit_contact') : __('contacts::messages.new_contact'));
    }
}
