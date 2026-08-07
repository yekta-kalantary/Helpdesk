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
        array $attributes,
        bool $portalEnabled,
        array $portalProfile,
        ?string $portalPassword,
    ): int {
        return DB::transaction(function () use ($id, $attributes, $portalEnabled, $portalProfile, $portalPassword): int {
            $current = $id ? $this->customers->find($id) : null;
            $userId = $current['user_id'] ?? null;

            if ($portalEnabled) {
                if ($userId) {
                    $this->portal->update(
                        $userId,
                        $attributes['name'],
                        $portalProfile['last_name'],
                        $attributes['email'],
                        $portalProfile['mobile'],
                        $portalPassword,
                    );
                } else {
                    $userId = $this->portal->create(
                        $attributes['name'],
                        $portalProfile['last_name'],
                        $attributes['email'],
                        $portalProfile['mobile'],
                        (string) $portalPassword,
                    );
                }
            } elseif ($userId) {
                $this->portal->deactivate($userId);
            }

            if ($id) {
                $this->customers->update($id, $attributes, $userId);

                return $id;
            }

            return $this->customers->create($attributes, $userId);
        });
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $customer = $this->customers->find($id);
            if ($customer['user_id']) {
                $this->portal->deactivate($customer['user_id']);
            }
            $this->customers->delete($id);
        });
    }
}
