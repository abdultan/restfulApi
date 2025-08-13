<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Event;
use App\Models\Seat;
use App\Models\Rezervation;
use App\Models\RezervationItem;
use App\Models\Ticket;
use Illuminate\Support\Str;


class RezervationAndItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seats = Seat::where('status','reserved')->get();
        if ($seats->isEmpty()) return;

        $users = User::all();
        $eventByVenue = Event::all()->groupBy('venue_id');

        $groups = [];

        foreach ($seats as $seat) {
            $events = $eventByVenue->get($seat->venue_id);
            if (!$events || $events->isEmpty()) continue;

            $event = $events->random();
            $user = $users->random();

            $key = $user->id.'|'.$event->id;
            if (!isset($groups[$key])) {
                $groups[$key] = ['user'=>$user, 'event'=>$event, 'seats'=>[]];
            }
            $groups[$key]['seats'][] = $seat;
        }

        foreach ($groups as $g) {
            $total = collect($g['seats'])->sum('price');

            $rez = Rezervation::factory()->create([
                'user_id'      => $g['user']->id,
                'event_id'     => $g['event']->id,
                'status'       => 'confirmed',
                'total_amount' => $total,
            ]);

            foreach ($g['seats'] as $seat) {
                RezervationItem::create([
                    'rezervation_id' => $rez->id,
                    'seat_id'        => $seat->id,
                    'price'          => $seat->price,
                ]);

                Ticket::create([
                    'rezervation_id' => $rez->id,
                    'seat_id'        => $seat->id,
                    'ticket_code'    => strtoupper(Str::random(10)),
                    'status'         => 'active',
                ]);

                $seat->update(['status' => 'sold']);
            }
        }
    }
    
}
