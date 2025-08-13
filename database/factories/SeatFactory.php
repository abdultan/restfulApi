<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Seat>
 */
class SeatFactory extends Factory
{
    public function definition()
    {
        return [
            'status' => $this->faker->randomElement(['available','reserved']),
        ];
    }
}
