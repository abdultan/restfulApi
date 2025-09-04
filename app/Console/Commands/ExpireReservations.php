<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Rezervation;
use App\Models\Seat;
use Carbon\Carbon;

class ExpireReservations extends Command
{





    protected $signature = 'reservations:expire';






    protected $description = 'Expire old reservations and release seats';






    public function handle()
    {
        $expired = Rezervation::where('status', 'pending')
            ->where('expires_at', '<', Carbon::now())
            ->get();

        foreach ($expired as $rez) {
            $rez->update(['status' => 'expired']);

            Seat::whereIn('id', $rez->items->pluck('seat_id'))
                ->update([
                    'status'         => Seat::STATUS_AVAILABLE,
                    'reserved_by'    => null,
                    'reserved_until' => null,
                ]);

            $this->info("Rezervation {$rez->id} expired and seats released.");
        }
    }
}
