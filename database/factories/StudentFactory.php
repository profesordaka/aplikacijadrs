<?php

namespace Database\Factories;

use App\Models\NivoStudija;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ime' => $this->faker->firstName(),
            'prezime' => $this->faker->lastName(),
            'br_indexa' => $this->faker->unique()->bothify('##/##'),
            'datum_rodjenja' => $this->faker->date(),
            'telefon' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'godina_studija' => $this->faker->numberBetween(1, 4),
            'jmbg' => $this->faker->unique()->numerify('#############'),
            'nivo_studija_id' => NivoStudija::factory(),
            'pol' => $this->faker->randomElement(['M', 'Z']),
        ];
    }
}
