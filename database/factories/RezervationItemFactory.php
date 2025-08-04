<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\RezervationItem;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rezervation_item>
 */
class RezervationItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'price' => $this->faker->randomFloat(2, 0, 100)
        ];
    }
}
