<?php

namespace Database\Factories;

use App\Models\Edition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Edition>
 */
class EditionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Izdanje '.fake()->unique()->numberBetween(1, 9999),
            'about_title' => 'O projektu',
            'about_body' => fake()->paragraphs(2, true),
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => 'active', 'activated_at' => now()]);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => 'archived', 'activated_at' => now()->subMonths(6)]);
    }
}
