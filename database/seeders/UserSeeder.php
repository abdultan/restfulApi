<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Seat;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
<<<<<<< HEAD
        $reservedCount = Seat::where('status', 'reserved')->count();

        User::factory($reservedCount)->create();

=======
        // Create a default admin
        User::firstOrCreate(
            ['email' => 'admin@case.local'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Admin123!'),
                'role' => 'admin',
            ]
        );

        $reservedCount = Seat::where('status', 'reserved')->count();

        User::factory($reservedCount)->create();
>>>>>>> 6291303 (ticket ve event işlemleri yapıldı)
    }
}
