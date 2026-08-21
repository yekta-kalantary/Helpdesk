<?php

namespace Modules\Clients\Application\DTOs;

use Modules\Clients\Infrastructure\Models\Client;

final readonly class ClientSummary
{
    public function __construct(
        public int $id,
        public string $name,
    ) {}

    public static function fromModel(Client $client): self
    {
        return new self(
            id: $client->id,
            name: $client->name,
        );
    }
}
