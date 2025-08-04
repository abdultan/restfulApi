<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rezervation>
 */
class RezervationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled']),
            'total_amount' => 0,
            'expires_at' => now()->addMinutes(rand(30,120))
        ];
    }
}
