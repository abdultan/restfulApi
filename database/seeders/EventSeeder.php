<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Venue;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $venues = Venue::all();

        foreach($venues as $venue){
            $adet = rand(1,3);
            Event::factory($adet)->create([
                'venue_id' => $venue->id,
            ]);
        }
        
    }


}

