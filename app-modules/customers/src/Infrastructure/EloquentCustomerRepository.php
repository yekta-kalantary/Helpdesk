<?php

namespace Modules\Customers\Infrastructure;

use App\Enums\PersonType;
use App\Models\Person;
use Modules\Customers\Domain\Contracts\CustomerRepository;
use Modules\Customers\Infrastructure\Models\Customer;

class EloquentCustomerRepository implements CustomerRepository
{
    public function search(?string $term = null): array
    {
        return Customer::query()
            ->with('person.user')
            ->when($term, fn ($query) => $query->whereHas('person', fn ($person) => $person
                ->where('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('mobile', 'like', "%{$term}%")))
            ->latest('id')
            ->get()
            ->map(fn (Customer $customer) => $this->map($customer))
            ->all();
    }

    public function find(int $id): array
    {
        return $this->map(Customer::query()->with('person.user')->findOrFail($id));
    }

    public function create(array $personAttributes, array $customerAttributes): int
    {
        $person = Person::create([
            ...$personAttributes,
            'type' => PersonType::Customer,
        ]);

        return Customer::create([
            ...$customerAttributes,
            'person_id' => $person->id,
        ])->id;
    }

    public function update(int $id, array $personAttributes, array $customerAttributes): void
    {
        $customer = Customer::query()->with('person')->findOrFail($id);
        abort_unless($customer->person->type === PersonType::Customer, 409);

        $customer->person->update($personAttributes);
        $customer->update($customerAttributes);
    }

    public function delete(int $id): void
    {
        Customer::query()->findOrFail($id)->delete();
    }

    /** @return array<string,mixed> */
    private function map(Customer $customer): array
    {
        $user = $customer->person->user;

        return [
            'id' => $customer->id,
            'person_id' => $customer->person_id,
            'user_id' => $user?->id,
            'portal_active' => (bool) ($user?->is_active ?? false),
            'name' => $customer->person->first_name,
            'last_name' => $customer->person->last_name,
            'full_name' => $customer->person->full_name,
            'email' => $customer->person->email,
            'mobile' => $customer->person->mobile,
            'notes' => $customer->notes,
            'created_at' => $customer->created_at,
        ];
    }
}
