<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Seat;
use App\Models\RezervationItem;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SeatService
{
    /**
     * Attempt to block seats for a user and event.
     * Returns an array with either ['ok'=>true, 'blocked_seat_ids'=>[]]
     * or ['ok'=>false, 'code'=>int, 'message'=>string].
     */
    public function block(array $seatIds, int $eventId, int $userId): array
    {
        // Event time rule
        $event = Event::findOrFail($eventId);
        if (now()->greaterThanOrEqualTo($event->start_date)) {
            return [
                'ok' => false,
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Event already started',
            ];
        }

        return DB::transaction(function () use ($seatIds, $userId, $eventId) {
            $seats = Seat::whereIn('id', $seatIds)->lockForUpdate()->get();

            foreach ($seats as $seat) {
                if ($seat->status === Seat::STATUS_RESERVED && $seat->reserved_until && $seat->reserved_until->isPast()) {
                    $seat->update([
                        'status' => Seat::STATUS_AVAILABLE,
                        'reserved_by' => null,
                        'reserved_until' => null,
                    ]);
                }
            }

            $seats = Seat::whereIn('id', $seatIds)->lockForUpdate()->get();

            foreach ($seats as $seat) {
                if ($seat->status === Seat::STATUS_RESERVED) {
                    return [
                        'ok' => false,
                        'code' => Response::HTTP_CONFLICT,
                        'message' => "Seat {$seat->id} is on hold",
                    ];
                }

                $isSoldForEvent = $seat->tickets()
                    ->where('status', 'active')
                    ->whereHas('rezervation', function ($q) use ($eventId) {
                        $q->where('event_id', $eventId)->where('status', 'confirmed');
                    })
                    ->exists();
                if ($isSoldForEvent) {
                    return [
                        'ok' => false,
                        'code' => Response::HTTP_CONFLICT,
                        'message' => "Seat {$seat->id} already sold for this event",
                    ];
                }

                $hasPending = RezervationItem::where('seat_id', $seat->id)
                    ->whereHas('rezervation', function ($q) use ($eventId) {
                        $q->where('event_id', $eventId)
                          ->where('status', 'pending')
                          ->where('expires_at', '>', now());
                    })
                    ->exists();
                if ($hasPending) {
                    return [
                        'ok' => false,
                        'code' => Response::HTTP_CONFLICT,
                        'message' => "Seat {$seat->id} is reserved for this event",
                    ];
                }
            }

            Seat::whereIn('id', $seatIds)->update([
                'status' => Seat::STATUS_RESERVED,
                'reserved_by' => $userId,
                'reserved_until' => now()->addMinutes(15),
            ]);

            return [
                'ok' => true,
                'blocked_seat_ids' => $seats->pluck('id')->values(),
            ];
        });
    }

    /**
     * Release seats reserved by a user.
     */
    public function release(array $seatIds, int $userId): array
    {
        $affected = Seat::whereIn('id', $seatIds)
            ->where('status', Seat::STATUS_RESERVED)
            ->where('reserved_by', $userId)
            ->update([
                'status' => Seat::STATUS_AVAILABLE,
                'reserved_by' => null,
                'reserved_until' => null,
            ]);

        return [
            'ok' => true,
            'released_count' => $affected,
        ];
    }
}
