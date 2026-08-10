<?php

namespace Modules\Contacts\Presentation\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\Contacts\Domain\Contracts\ContactAccountGateway;
use Modules\Contacts\Domain\Contracts\ContactRepository;

class Index extends Component
{
    #[Url(as: 'q', except: '')]
    public string $q = '';

    protected ContactRepository $contacts;

    protected ContactAccountGateway $accounts;

    public function boot(ContactRepository $contacts, ContactAccountGateway $accounts): void
    {
        $this->contacts = $contacts;
        $this->accounts = $accounts;
    }

    public function render()
    {
        $contacts = $this->contacts->search(trim($this->q) ?: null);
        $canViewAccounts = (bool) auth()->user()?->can('users.view');
        $accountStates = $canViewAccounts
            ? $this->accounts->enabledFor(array_map(static fn (array $contact): int => (int) $contact['id'], $contacts))
            : [];

        foreach ($contacts as &$contact) {
            $contact['account_enabled'] = $canViewAccounts
                ? ($accountStates[(int) $contact['id']] ?? false)
                : null;
        }
        unset($contact);

        return view('contacts::index', [
            'contacts' => $contacts,
            'canViewAccounts' => $canViewAccounts,
        ])->title(__('contacts::messages.contacts'));
    }
}
