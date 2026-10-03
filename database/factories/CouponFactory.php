<?php

namespace Database\Factories;

use App\Models\Coupon;
use App\Models\Edition;
use App\Models\Titular;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    public function definition(): array
    {
        return [
            'edition_id' => Edition::factory(),
            'titular_id' => Titular::factory(),
            'title' => fake()->sentence(3),
            'discount' => fake()->numberBetween(5, 50).'%',
            'is_published' => true,
            'requires_login' => false,
        ];
    }

    public function loginOnly(): static
    {
        return $this->state(fn () => ['requires_login' => true]);
    }
}
