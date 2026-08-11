<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Domain\Enums\ClientStatus;
use Modules\Clients\Infrastructure\Models\Client;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'description' => null,
            'status' => ClientStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => ClientStatus::Inactive]);
    }
}
