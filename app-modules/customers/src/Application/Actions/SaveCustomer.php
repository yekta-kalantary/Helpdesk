<?php

namespace Modules\Customers\Application\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Customers\Domain\Contracts\CustomerPortalAccount;
use Modules\Customers\Domain\Contracts\CustomerRepository;

class SaveCustomer
{
    public function __construct(
        private readonly CustomerRepository $customers,
        private readonly CustomerPortalAccount $portal,
    ) {}

    public function execute(
        ?int $id,
        array $personAttributes,
        array $customerAttributes,
        bool $portalEnabled,
        ?string $portalPassword,
    ): int {
        return DB::transaction(function () use ($id, $personAttributes, $customerAttributes, $portalEnabled, $portalPassword): int {
            if ($id) {
                $this->customers->update($id, $personAttributes, $customerAttributes);
                $customerId = $id;
            } else {
                $customerId = $this->customers->create($personAttributes, $customerAttributes);
            }

            $customer = $this->customers->find($customerId);

            if ($portalEnabled) {
                $this->portal->enable($customer['person_id'], $portalPassword);
            } else {
                $this->portal->disable($customer['person_id']);
            }

            return $customerId;
        });
    }
}
