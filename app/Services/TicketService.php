<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\Rezervation;
use App\Models\RezervationItem;
use App\Models\Seat;
use Illuminate\Support\Facades\DB;

class TicketService
{
    /**
     * Transfer ticket to another user.
     */
    public function transfer(Ticket $ticket, int $toUserId): Ticket
    {
        return DB::transaction(function () use ($ticket, $toUserId) {
            // Lock ticket for update to prevent race conditions
            $ticket = $ticket->lockForUpdate()->load(['rezervation.event', 'seat']);

            // Get price from reservation item or seat
            $itemPrice = RezervationItem::where('rezervation_id', $ticket->rezervation_id)
                ->where('seat_id', $ticket->seat_id)
                ->value('price');
            $price = $itemPrice ?? $ticket->seat->price ?? 0;

            // Create new reservation for target user
            $newRezervation = Rezervation::create([
                'user_id' => $toUserId,
                'event_id' => $ticket->rezervation->event->id,
                'status' => 'confirmed',
                'total_amount' => $price,
                'expires_at' => null,
            ]);

            // Create new reservation item
            RezervationItem::create([
                'rezervation_id' => $newRezervation->id,
                'seat_id' => $ticket->seat_id,
                'price' => $price,
            ]);

            // Update ticket with new reservation and status
            $ticket->update([
                'rezervation_id' => $newRezervation->id,
                'status' => Ticket::STATUS_TRANSFERRED,
            ]);

            return $ticket->fresh(['rezervation.event', 'seat']);
        });
    }

    /**
     * Cancel ticket and make seat available again.
     */
    public function cancel(Ticket $ticket): Ticket
    {
        return DB::transaction(function () use ($ticket) {
            // Lock ticket for update
            $ticket = $ticket->lockForUpdate()->load(['rezervation.event', 'seat']);

            // Update ticket status
            $ticket->update(['status' => Ticket::STATUS_CANCELLED]);

            // Make seat available again
            Seat::where('id', $ticket->seat_id)->update([
                'status' => Seat::STATUS_AVAILABLE,
                'reserved_by' => null,
                'reserved_until' => null,
            ]);

            return $ticket->fresh(['rezervation.event', 'seat']);
        });
    }
}
