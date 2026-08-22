<?php

namespace Modules\Identity\Application;

use Modules\Clients\Application\Contracts\ClientStatusQuery;
use Modules\Identity\Application\Contracts\AccountDirectory;
use Modules\Identity\Application\DTOs\AccountSummary;
use Modules\Identity\Domain\Enums\UserRole;

final class AccountAuthenticationEligibility
{
    public function __construct(
        private AccountDirectory $accounts,
        private ClientStatusQuery $clients,
    ) {}

    public function canAuthenticateAccount(int $accountId): bool
    {
        $account = $this->accounts->find($accountId);

        return $account !== null && $this->canAuthenticate($account);
    }

    public function canAuthenticate(AccountSummary $account): bool
    {
        return $account->isActive
            && ($account->role !== UserRole::Customer
                || ($account->clientId !== null && $this->clients->find($account->clientId)?->isActive === true));
    }
}
