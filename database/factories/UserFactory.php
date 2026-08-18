<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Domain\Enums\UserRole;
use Modules\Identity\Infrastructure\Models\User;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'role' => UserRole::Customer,
            'name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'mobile' => fake()->optional()->numerify('09#########'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'is_active' => true,
            'last_login_at' => null,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'client_id' => null,
            'role' => UserRole::Admin,
        ]);
    }

    public function customer(Client $client): static
    {
        return $this->state(fn () => [
            'client_id' => $client->id,
            'role' => UserRole::Customer,
        ]);
    }

    public function employee(Client $client): static
    {
        return $this->state(fn () => [
            'client_id' => $client->id,
            'role' => UserRole::Employee,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }
}
