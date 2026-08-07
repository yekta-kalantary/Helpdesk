<?php

namespace Database\Factories;

use App\Enums\PersonType;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Person>
 */
class PersonFactory extends Factory
{
    protected $model = Person::class;

    public function definition(): array
    {
        return [
            'type' => PersonType::Employee,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'mobile' => fake()->unique()->numerify('09#########'),
        ];
    }

    public function customer(): static
    {
        return $this->state(fn (): array => ['type' => PersonType::Customer]);
    }
}
