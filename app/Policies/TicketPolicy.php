<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;

class TicketPolicy
{
    /**
     * Determine whether the user can view the ticket.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        return $ticket->rezervation->user_id === $user->id;
    }

    /**
     * Determine whether the user can transfer the ticket.
     */
    public function transfer(User $user, Ticket $ticket): bool
    {
        // Check ownership
        if ($ticket->rezervation->user_id !== $user->id) {
            return false;
        }

        // Check status
        if (!in_array($ticket->status, [Ticket::STATUS_ACTIVE, Ticket::STATUS_TRANSFERRED], true)) {
            return false;
        }

        // Check if event is in the future
        $startDate = Carbon::parse($ticket->rezervation->event->start_date);
        return $startDate->isFuture();
    }

    /**
     * Determine whether the user can cancel the ticket.
     */
    public function cancel(User $user, Ticket $ticket): bool
    {
        // Check ownership
        if ($ticket->rezervation->user_id !== $user->id) {
            return false;
        }

        // Check status
        if (!in_array($ticket->status, [Ticket::STATUS_ACTIVE, Ticket::STATUS_TRANSFERRED], true)) {
            return false;
        }

        // Check cancellation window (24 hours before event)
        $startDate = Carbon::parse($ticket->rezervation->event->start_date);
        $minHours = config('tickets.cancel_min_hours_before', 24);
        
        return now()->diffInHours($startDate, false) >= $minHours;
    }
}
