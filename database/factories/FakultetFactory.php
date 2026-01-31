<?php

namespace Database\Factories;

use App\Models\Univerzitet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fakultet>
 */
class FakultetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'naziv' => $this->faker->company() . ' Fakultet',
            'email' => $this->faker->unique()->safeEmail(),
            'telefon' => $this->faker->phoneNumber(),
            'web' => $this->faker->url(),
            'univerzitet_id' => Univerzitet::factory(),
        ];
    }
}
