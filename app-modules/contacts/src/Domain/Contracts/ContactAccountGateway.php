<?php

namespace Modules\Contacts\Domain\Contracts;

interface ContactAccountGateway
{
    /** @return array{user_id:?int,account_enabled:bool,role:?string} */
    public function get(int $contactId): array;

    /** @param array<int, int> $contactIds @return array<int, bool> */
    public function enabledFor(array $contactIds): array;

    /** @return array<int, string> */
    public function assignableRoles(?int $contactId): array;

    /** @param array{enabled:bool,role:?string,password:?string} $account */
    public function save(int $contactId, array $account): void;
}
