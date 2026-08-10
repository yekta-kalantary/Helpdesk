<?php

namespace Modules\Contacts\Presentation\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\Contacts\Domain\Contracts\ContactRepository;

class Index extends Component
{
    #[Url(as: 'q', except: '')]
    public string $q = '';

    protected ContactRepository $contacts;

    public function boot(ContactRepository $contacts): void
    {
        $this->contacts = $contacts;
    }

    public function render()
    {
        return view('contacts::index', [
            'contacts' => $this->contacts->search(trim($this->q) ?: null),
        ])->title(__('contacts::messages.contacts'));
    }
}
