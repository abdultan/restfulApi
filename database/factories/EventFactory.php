<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
<<<<<<< HEAD
        return [
            'name' => $this->faker->randomElement([
            'Rock Fest', 'Tiyatro Gecesi', 'Jazz Konseri', 'Stand-up Show',
            'Klasik Müzik Akşamı', 'Tek Kişilik Gösteri', 'Bale Gösterisi',
            'Film Galası', 'Elektronik Gece', 'Anadolu Ezgileri'
            ]),
            'description' => $this->faker->text,
            'start_date' => $this->faker->dateTime,
            'end_date' => $this->faker->dateTime,
=======
        $start = \Carbon\Carbon::instance($this->faker->dateTimeBetween('+1 days', '+60 days'));
        $end   = (clone $start)->addHours(2);

        return [
            'name' => $this->faker->randomElement([
                'Rock Fest', 'Tiyatro Gecesi', 'Jazz Konseri', 'Stand-up Show',
                'Klasik Müzik Akşamı', 'Tek Kişilik Gösteri', 'Bale Gösterisi',
                'Film Galası', 'Elektronik Gece', 'Anadolu Ezgileri'
            ]),
            'description' => $this->faker->text,
            'start_date' => $start,
            'end_date'   => $end,
>>>>>>> 6291303 (ticket ve event işlemleri yapıldı)
            'status' => $this->faker->randomElement(['draft', 'published', 'archived'])
        ];
    }
}
