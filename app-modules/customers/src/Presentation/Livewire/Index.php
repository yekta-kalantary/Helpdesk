<?php

namespace Modules\Customers\Presentation\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\Customers\Domain\Contracts\CustomerRepository;

class Index extends Component
{
    #[Url(as: 'q', except: '')]
    public string $q = '';

    protected CustomerRepository $customers;

    public function boot(CustomerRepository $customers): void
    {
        $this->customers = $customers;
    }

    public function render()
    {
        return view('customers::index', [
            'customers' => $this->customers->search(trim($this->q) ?: null),
        ])->title(__('customers::messages.customers'));
    }
}
