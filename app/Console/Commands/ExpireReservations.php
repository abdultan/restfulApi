<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Rezervation;
use App\Models\Seat;
use Carbon\Carbon;

class ExpireReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire old reservations and release seats';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $expired = Rezervation::where('status', 'pending')
            ->where('expires_at', '<', Carbon::now())
            ->get();

        foreach ($expired as $rez) {
            $rez->update(['status' => 'expired']);

            // ilgili koltukları serbest bırak ve hold bilgilerini temizle
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
