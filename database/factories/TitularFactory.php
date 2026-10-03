<?php

namespace Database\Factories;

use App\Models\Titular;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Titular>
 */
class TitularFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
        ];
    }
}
