<?php

namespace Database\Factories;

use App\Models\Fakultet;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MappingRequest>
 */
class MappingRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'professor_id' => User::factory(),
            'fakultet_id' => Fakultet::factory(),
            'student_id' => Student::factory(),
            'status' => $this->faker->randomElement(['pending', 'accepted', 'rejected']),
            'datum_finalizacije' => $this->faker->optional()->date(),
            'napomena' => $this->faker->optional()->sentence(),
        ];
    }
}
