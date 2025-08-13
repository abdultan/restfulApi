<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            VenueSeeder::class,
            EventSeeder::class,
            SeatSeeder::class,
            UserSeeder::class,
            RezervationAndItemSeeder::class,
        ]);
    }
}
