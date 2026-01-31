<?php

namespace Database\Factories;

use App\Models\Fakultet;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mobilnost>
 */
class MobilnostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'fakultet_id' => Fakultet::factory(),
            'datum_pocetka' => $this->faker->date(),
            'datum_kraja' => $this->faker->date(),
            'is_locked' => false,
            'tip_mobilnosti' => $this->faker->randomElement(['erasmus', 'ceepus', 'bilateral']),
        ];
    }
}
