<?php

namespace Modules\Customers\Infrastructure;

use Illuminate\Support\Facades\DB;
use Modules\Customers\Domain\Contracts\CustomerRepository;
use Modules\Customers\Infrastructure\Models\Customer;

class EloquentCustomerRepository implements CustomerRepository
{
    public function search(?string $term = null): array
    {
        return Customer::query()
            ->when($term, fn ($query) => $query->where(fn ($nested) => $nested
                ->where('name', 'like', "%{$term}%")
                ->orWhere('company', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")))
            ->latest('id')
            ->get()
            ->map(fn (Customer $customer) => $this->map($customer))
            ->all();
    }

    public function find(int $id): array
    {
        return $this->map(Customer::query()->findOrFail($id));
    }

    public function create(array $attributes, ?int $userId): int
    {
        $customer = Customer::create([...$attributes, 'user_id' => $userId]);

        return $customer->id;
    }

    public function update(int $id, array $attributes, ?int $userId): void
    {
        Customer::query()->findOrFail($id)->update([...$attributes, 'user_id' => $userId]);
    }

    public function delete(int $id): void
    {
        Customer::query()->findOrFail($id)->delete();
    }

    /** @return array<string,mixed> */
    private function map(Customer $customer): array
    {
        $portalActive = $customer->user_id
            ? (bool) DB::table('users')->where('id', $customer->user_id)->value('is_active')
            : false;

        return [
            'id' => $customer->id,
            'user_id' => $customer->user_id,
            'portal_active' => $portalActive,
            'name' => $customer->name,
            'company' => $customer->company,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'notes' => $customer->notes,
            'status' => $customer->status->value,
            'created_at' => $customer->created_at,
        ];
    }
}
