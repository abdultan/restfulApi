<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RezervationItem;
use App\Models\Rezervation;
use App\Models\Seat;

class RezervationItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RezervationItem::factory()->count(10)->create();
    }
}
