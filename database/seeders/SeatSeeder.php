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
    $sections = range('A', 'K'); // ['A', ..., 'K']
    $venues = Venue::all();

    foreach ($venues as $venue) {
        foreach ($sections as $section) {
            foreach (range(1, 10) as $row) {
                foreach (range(1, 20) as $number) {

                   
                    $base_price = 1000;
                    $sectionIndex = array_search($section, $sections); // 0-10
                    $price = $base_price - ($sectionIndex * 10) - ($row * 10);

                    
                    Seat::factory()->create([
                        'venue_id' => $venue->id,
                        'section' => $section,
                        'row' => $row,
                        'number' => $number,
                        'price' => $price
                    ]);
                }
            }
        }
    }
}
}