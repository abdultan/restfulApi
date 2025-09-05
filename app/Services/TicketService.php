<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\Rezervation;
use App\Models\RezervationItem;
use App\Models\Seat;
use App\Models\User;
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
            $ticket = Ticket::where('id', $ticket->id)->lockForUpdate()->first();
            $ticket->load(['rezervation.event', 'seat']);

            // Get price from reservation item or seat
            $reservationItem = RezervationItem::where('rezervation_id', $ticket->rezervation_id)
                ->where('seat_id', $ticket->seat_id)
                ->first();
            
            $price = $reservationItem ? $reservationItem->price : ($ticket->seat->price ?? 0);

            $newRezervation = Rezervation::create([
                'user_id' => $toUserId,
                'event_id' => $ticket->rezervation->event->id,
                'status' => 'confirmed',
                'total_amount' => $price,
                'expires_at' => now()->addDays(30), 
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
     * Transfer ticket to another user by email.
     */
    public function transferToEmail(Ticket $ticket, string $email): Ticket
    {
        return DB::transaction(function () use ($ticket, $email) {
            $targetUser = User::where('email', $email)->first();
            if (!$targetUser) {
                throw new \Exception("User with email '{$email}' not found.");
            }

            return $this->transfer($ticket, $targetUser->id);
        });
    }

    /**
     * Cancel ticket and make seat available again.
     */
    public function cancel(Ticket $ticket): Ticket
    {
        return DB::transaction(function () use ($ticket) {
            // Lock ticket for update
            $ticket = Ticket::where('id', $ticket->id)->lockForUpdate()->first();
            $ticket->load(['rezervation.event', 'seat']);

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
