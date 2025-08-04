<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rezervation;
use App\Models\Event;
use App\Models\User;
class RezervationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $events = Event::all();
        $users = User::all();
        foreach($events as $event){
            Rezervation::factory()->create([
                'event_id' => $event->id,
                'user_id'=>$user->id
            ]);
        }
    }
}
