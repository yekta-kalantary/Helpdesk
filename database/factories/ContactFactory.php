<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Contacts\Infrastructure\Models\Contact;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'gender' => null,
            'email' => fake()->unique()->safeEmail(),
            'mobile' => fake()->unique()->numerify('09#########'),
            'province' => null,
            'city' => null,
            'address' => null,
            'postal_code' => null,
        ];
    }
}
