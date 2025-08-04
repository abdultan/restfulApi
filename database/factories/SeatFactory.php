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
        $section = $this->faker->randomElement(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J','K']);

        $row = $this->faker->numberBetween(1, 10);
    
        $index = array_search($section,['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J','K']);
    
        $number = $this->faker->numberBetween(1, 20);
    
        $base_price = 1000;
        $rowDecrease = $row == 1 ? 0 : $row*10;
        $sectionDecrease = $index *10;
        $price = $base_price - $sectionDecrease - $rowDecrease;

        return [
            'row' => $row,
            'number' => $number,
            'price' => $price,
            'section' => $section,
            'status' => $this->faker->randomElement(['available', 'sold', 'reserved'])
        ];
    }
}
