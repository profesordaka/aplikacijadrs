<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UniverzitetFactory extends Factory
{
    protected $model = \App\Models\Univerzitet::class;

    public function definition()
    {
        return [
           'naziv' => $this->faker->company,
            'drzava' => $this->faker->country,
            'grad' => $this->faker->city,
            'email' => $this->faker->unique()->safeEmail,
        ];
    }
}
