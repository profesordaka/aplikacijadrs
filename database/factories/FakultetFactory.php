<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Univerzitet;

class FakultetFactory extends Factory
{
    protected $model = \App\Models\Fakultet::class;

    public function definition()
    {
        return [
            'naziv' => $this->faker->company,
            'email' => $this->faker->unique()->companyEmail,
            'telefon' => $this->faker->phoneNumber,
            'web' => $this->faker->url,
            'univerzitet_id' => Univerzitet::factory(), // ili postojeći univerzitet id
            'uputstvo_za_ocjene' => $this->faker->sentence,
            'uputstvo_file' => null,
        ];
    }
}
