<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Seat;
use App\Models\Venue;

class SeatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $venues = Venue::all();

        $sections = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];

        foreach ($venues as $venue) {
            foreach ($sections as $section) {
                for ($row = 1; $row <= 10; $row++) {
                    for ($number = 1; $number <= 20; $number++) {
                        Seat::factory()->create([
                            'venue_id' => $venue->id
                        ]);
                    }
                }
            }
        }
    }
}