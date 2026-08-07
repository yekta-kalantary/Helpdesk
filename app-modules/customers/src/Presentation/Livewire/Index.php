<?php

namespace Modules\Customers\Presentation\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\Customers\Application\Actions\SaveCustomer;
use Modules\Customers\Domain\Contracts\CustomerRepository;

class Index extends Component
{
    #[Url(as: 'q', except: '')]
    public string $q = '';

    protected CustomerRepository $customers;

    protected SaveCustomer $saveCustomer;

    public function boot(CustomerRepository $customers, SaveCustomer $saveCustomer): void
    {
        $this->customers = $customers;
        $this->saveCustomer = $saveCustomer;
    }

    public function delete(int $customer): void
    {
        abort_unless(auth()->user()?->can('customers.delete'), 403);

        $this->saveCustomer->delete($customer);
        session()->flash('success', __('app.deleted_successfully'));
    }

    public function render()
    {
        return view('customers::index', [
            'customers' => $this->customers->search(trim($this->q) ?: null),
        ])->title(__('customers::messages.customers'));
    }
}
