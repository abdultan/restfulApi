<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Rezervation;
use App\Models\RezervationItem;
use App\Models\Seat;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RezervationService
{
    public function listForUser(int $userId, int $perPage = 10)
    {
        return Rezervation::with(['items.seat', 'event'])
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function store(int $userId, int $eventId, array $seatIds)
    {
        $evt = Event::findOrFail($eventId);
        if ($evt->status !== 'published') {
            return ['ok' => false, 'code' => Response::HTTP_UNPROCESSABLE_ENTITY, 'message' => 'Event is not published'];
        }
        if (now()->greaterThanOrEqualTo($evt->start_date)) {
            return ['ok' => false, 'code' => Response::HTTP_UNPROCESSABLE_ENTITY, 'message' => 'Event already started'];
        }

        return DB::transaction(function () use ($userId, $eventId, $seatIds) {
            $seats = Seat::whereIn('id', $seatIds)->lockForUpdate()->get();

            if ($seats->count() !== count($seatIds)) {
                return ['ok' => false, 'code' => Response::HTTP_NOT_FOUND, 'message' => 'Some seats not found'];
            }

            $venueIds = $seats->pluck('venue_id')->unique();
            if ($venueIds->count() !== 1) {
                return ['ok' => false, 'code' => Response::HTTP_UNPROCESSABLE_ENTITY, 'message' => 'Selected seats must belong to the same venue'];
            }

            $event = Event::find($eventId);
            if (!$event || (int) $event->venue_id !== (int) $venueIds->first()) {
                return ['ok' => false, 'code' => Response::HTTP_UNPROCESSABLE_ENTITY, 'message' => 'Seats do not belong to the given event'];
            }

            $duplicate = RezervationItem::whereIn('seat_id', $seatIds)
                ->whereHas('rezervation', function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                        ->where('status', 'pending');
                })
                ->first();

            if ($duplicate) {
                return ['ok' => false, 'code' => Response::HTTP_CONFLICT, 'message' => "Seat {$duplicate->seat_id} already exists in your pending reservation", 'reservation_id' => $duplicate->rezervation_id];
            }

            foreach ($seats as $s) {
                if ($s->status !== Seat::STATUS_RESERVED) {
                    return ['ok' => false, 'code' => Response::HTTP_CONFLICT, 'message' => "Seat {$s->id} is not reserved"];
                }
                if ((int) $s->reserved_by !== (int) $userId) {
                    return ['ok' => false, 'code' => Response::HTTP_FORBIDDEN, 'message' => "Seat {$s->id} reserved by another user"];
                }
                if ($s->reserved_until && now()->greaterThan($s->reserved_until)) {
                    return ['ok' => false, 'code' => Response::HTTP_CONFLICT, 'message' => "Seat {$s->id} hold expired"];
                }
            }

            $total = $seats->sum('price');

            $rez = Rezervation::create([
                'user_id' => $userId,
                'event_id' => $eventId,
                'status' => 'pending',
                'total_amount' => $total,
                'expires_at' => now()->addMinutes(15),
            ]);

            foreach ($seats as $s) {
                RezervationItem::create([
                    'rezervation_id' => $rez->id,
                    'seat_id' => $s->id,
                    'price' => $s->price,
                ]);
            }

            return [
                'ok' => true,
                'rezervation' => $rez->fresh(['items.seat', 'event']),
            ];
        });
    }

    public function confirm(int $rezervationId, int $userId)
    {
        return DB::transaction(function () use ($rezervationId, $userId) {
            $rez = Rezervation::with(['items', 'event'])
                ->lockForUpdate()
                ->find($rezervationId);

            if (!$rez || $rez->user_id !== $userId) {
                return ['ok' => false, 'code' => Response::HTTP_NOT_FOUND, 'message' => 'Reservation not found'];
            }
            if ($rez->status !== 'pending') {
                return ['ok' => false, 'code' => Response::HTTP_CONFLICT, 'message' => 'Reservation is not pending'];
            }
            if ($rez->event->status !== 'published') {
                return ['ok' => false, 'code' => Response::HTTP_UNPROCESSABLE_ENTITY, 'message' => 'Event is not published'];
            }
            if (now()->greaterThanOrEqualTo($rez->event->start_date)) {
                return ['ok' => false, 'code' => Response::HTTP_UNPROCESSABLE_ENTITY, 'message' => 'Event already started'];
            }
            if ($rez->expires_at && now()->greaterThan($rez->expires_at)) {
                return ['ok' => false, 'code' => Response::HTTP_CONFLICT, 'message' => 'Reservation expired'];
            }

            $seatIds = $rez->items->pluck('seat_id')->sort()->values();
            $seats = Seat::whereIn('id', $seatIds)->lockForUpdate()->get();

            foreach ($seats as $s) {
                if ($s->status !== Seat::STATUS_RESERVED) {
                    return ['ok' => false, 'code' => Response::HTTP_CONFLICT, 'message' => "Seat {$s->id} is not reserved"];
                }
                if ((int) $s->reserved_by !== (int) $userId) {
                    return ['ok' => false, 'code' => Response::HTTP_FORBIDDEN, 'message' => "Seat {$s->id} reserved by another user"];
                }
                if ($s->reserved_until && now()->greaterThan($s->reserved_until)) {
                    return ['ok' => false, 'code' => Response::HTTP_CONFLICT, 'message' => "Seat {$s->id} hold expired"];
                }
            }

            Seat::whereIn('id', $seatIds)->update([
                'status' => Seat::STATUS_SOLD,
                'reserved_by' => null,
                'reserved_until' => null,
            ]);

            $tickets = [];
            foreach ($seatIds as $sid) {
                $tickets[] = Ticket::create([
                    'rezervation_id' => $rez->id,
                    'seat_id' => $sid,
                    'ticket_code' => Str::upper(Str::random(10)),
                    'status' => 'active',
                ]);
            }

            $rez->update(['status' => 'confirmed']);

            return [
                'ok' => true,
                'rezervation' => $rez->fresh(['items.seat', 'event']),
                'tickets' => collect($tickets)->map(fn($t) => [
                    'id' => $t->id,
                    'seat_id' => $t->seat_id,
                    'ticket_code' => $t->ticket_code,
                    'status' => $t->status,
                ]),
            ];
        });
    }

    public function cancel(int $rezervationId, int $userId)
    {
        $rez = Rezervation::with('items')
            ->where('id', $rezervationId)
            ->where('user_id', $userId)
            ->first();

        if (!$rez) {
            return ['ok' => false, 'code' => Response::HTTP_NOT_FOUND, 'message' => 'Reservation not found'];
        }
        if ($rez->status !== 'pending') {
            return ['ok' => false, 'code' => Response::HTTP_CONFLICT, 'message' => 'Only pending reservations can be cancelled'];
        }

        DB::transaction(function () use ($rez) {
            $seatIds = $rez->items->pluck('seat_id');

            Seat::whereIn('id', $seatIds)->update([
                'status' => Seat::STATUS_AVAILABLE,
                'reserved_by' => null,
                'reserved_until' => null,
            ]);

            $rez->update(['status' => 'cancelled']);
        });

        return ['ok' => true];
    }
}
