<?php

namespace Modules\Customers\Presentation\Livewire;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Modules\Customers\Application\Actions\SaveCustomer;
use Modules\Customers\Domain\Contracts\CustomerRepository;
use Modules\Customers\Domain\Enums\CustomerStatus;

class Form extends Component
{
    #[Locked]
    public ?int $customerId = null;

    #[Locked]
    public ?int $personId = null;

    #[Locked]
    public ?int $portalUserId = null;

    public string $name = '';

    public string $last_name = '';

    public string $email = '';

    public string $mobile = '';

    public ?string $notes = null;

    public string $status = 'active';

    public bool $portal_enabled = false;

    public string $portal_password = '';

    public string $portal_password_confirmation = '';

    protected CustomerRepository $customers;

    protected SaveCustomer $saveCustomer;

    public function boot(CustomerRepository $customers, SaveCustomer $saveCustomer): void
    {
        $this->customers = $customers;
        $this->saveCustomer = $saveCustomer;
    }

    public function mount(?int $customer = null): void
    {
        if (! $customer) {
            return;
        }

        $item = $this->customers->find($customer);

        $this->customerId = $customer;
        $this->personId = $item['person_id'];
        $this->portalUserId = $item['user_id'];
        $this->name = $item['name'];
        $this->last_name = $item['last_name'];
        $this->email = $item['email'];
        $this->mobile = $item['mobile'];
        $this->notes = $item['notes'];
        $this->status = $item['status'];
        $this->portal_enabled = $item['portal_active'];
    }

    public function save()
    {
        abort_unless(
            auth()->user()?->can($this->customerId ? 'customers.update' : 'customers.create'),
            403,
        );

        $data = $this->validate();

        $this->saveCustomer->execute(
            $this->customerId,
            [
                'first_name' => $data['name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'mobile' => $data['mobile'],
            ],
            [
                'notes' => $data['notes'] ?: null,
                'status' => $data['status'],
            ],
            $data['portal_enabled'],
            $data['portal_password'] ?: null,
        );

        session()->flash('success', $this->customerId ? __('app.updated_successfully') : __('app.created_successfully'));

        return $this->redirectRoute('customers.index', navigate: true);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('people', 'email')->ignore($this->personId)],
            'mobile' => ['required', 'string', 'max:32'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(CustomerStatus::class)],
            'portal_enabled' => ['boolean'],
            'portal_password' => [$this->portal_enabled && ! $this->portalUserId ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function render()
    {
        return view('customers::form', [
            'statuses' => CustomerStatus::cases(),
        ])->title($this->customerId ? __('customers::messages.edit_customer') : __('customers::messages.new_customer'));
    }
}
