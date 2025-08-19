<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use Carbon\Carbon;

class EventShiftFutureSeeder extends Seeder
{
    public function run()
    {
        foreach (Event::all() as $e) {
            if (Carbon::parse($e->start_date)->isPast()) {
                $start = now()->addDays(rand(1, 30));
                $end   = (clone $start)->addHours(2);
                $e->update(['start_date' => $start, 'end_date' => $end]);
            }
        }
    }
}


