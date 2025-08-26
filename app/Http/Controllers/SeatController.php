<?php

namespace App\Http\Controllers;

use App\Http\Requests\SeatBlockRequest;
use Illuminate\Http\Request;
use App\Http\Requests\SeatReleaseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Seat;
use App\Models\Event;
use App\Models\RezervationItem;
use App\Traits\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class SeatController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function block(SeatBlockRequest $request): JsonResponse {
        $ids = $request->validated('seat_ids');
        $eventId = (int) $request->validated('event_id');
        $userId = $request->user()->id;

        // Etkinlik zamanı kontrolü (başlamamış olmalı)
        $event = Event::findOrFail($eventId);
        if (now()->greaterThanOrEqualTo($event->start_date)) {
            return $this->errorResponse('Event already started', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return DB::transaction(function () use ($ids,$userId,$eventId) {
            $seats = Seat::whereIn('id', $ids)->lockForUpdate()->get();

            foreach ($seats as $s) {
                if ($s->status === Seat::STATUS_RESERVED && $s->reserved_until && $s->reserved_until->isPast()) {
                    $s->update([
                        'status'         => Seat::STATUS_AVAILABLE,
                        'reserved_by'    => null,
                        'reserved_until' => null,
                    ]);
                }
            }
            $seats = Seat::whereIn('id', $ids)->lockForUpdate()->get();

            foreach ($seats as $s) {
                // Global hold kontrolü (yalnızca aktif hold'lar engeller)
                if ($s->status === Seat::STATUS_RESERVED) {
                    return $this->errorResponse("Seat {$s->id} is on hold", Response::HTTP_CONFLICT);
                }

                // Event'e göre satılık mı? (ticket/confirmed varsa blocklanamaz)
                $isSoldForEvent = $s->tickets()
                    ->where('status','active')
                    ->whereHas('rezervation', function($q) use ($eventId){
                        $q->where('event_id',$eventId)->where('status','confirmed');
                    })
                    ->exists();
                if ($isSoldForEvent) {
                    return $this->errorResponse("Seat {$s->id} already sold for this event", Response::HTTP_CONFLICT);
                }

                // Event'e göre bekleyen rez var mı? (pending + expires_at>now)
                $hasPending = $s->rezervationItems()
                    ->whereHas('rezervation', function($q) use ($eventId) {
                        $q->where('event_id',$eventId)
                          ->where('status', 'pending')
                          ->where('expires_at','>', now());
                    })
                    ->exists();
                if ($hasPending) {
                    return $this->errorResponse("Seat {$s->id} is reserved for this event", Response::HTTP_CONFLICT);
                }
                }

            Seat::whereIn('id', $ids)->update([
                'status'         => Seat::STATUS_RESERVED,
                'reserved_by'    => $userId,
                'reserved_until' => now()->addMinutes(15),
            ]);
            
            return $this->successResponse(
                ['blocked_seat_ids' => $seats->pluck('id')->values()],
                'Seats blocked successfully'
            );
        });
    }
    
    public function release(SeatReleaseRequest $request): JsonResponse
    {
        $ids = $request->validated('seat_ids');
        $userId = $request->user()->id;

        $affected = Seat::whereIn('id', $ids)
            ->where('status', Seat::STATUS_RESERVED)
            ->where('reserved_by', $userId)
            ->update([
                'status' => Seat::STATUS_AVAILABLE,
                'reserved_by' => null,
                'reserved_until' => null,]);

        return $this->successResponse(
            ['released_count' => $affected],
            'Seats released successfully'
        );
    }

    public function byEvent($eventId): JsonResponse
    {
    $event = Event::with('venue')->findOrFail($eventId);
    $now = now();

    $seats = Seat::where('venue_id', $event->venue_id)
        ->withCount([
            'tickets as sold_for_event' => function ($q) use ($eventId) {
                $q->where('status', 'active')
                  ->whereHas('rezervation', function ($r) use ($eventId) {
                      $r->where('event_id', $eventId)
                        ->where('status', 'confirmed');
                  });
            },

            'rezervationItems as pending_for_event' => function ($q) use ($eventId, $now) {
                $q->whereHas('rezervation', function ($r) use ($eventId, $now) {
                    $r->where('event_id', $eventId)
                      ->where('status', 'pending')
                      ->where('expires_at', '>', $now); // 15 dk kuralını dikkate al
                });
            },
        ])
        ->orderBy('section')->orderBy('row')->orderBy('number')
        ->paginate(100);

    $seats->getCollection()->transform(function ($s) {
        $eventStatus = $s->sold_for_event > 0 ? 'sold'
            : ($s->pending_for_event > 0 ? 'reserved' : 'available');

        // Response'ta karmaşa olmaması için global seat status'u maskele
        $s->status = $eventStatus;

        unset($s->sold_for_event, $s->pending_for_event);
        return $s;
    });

    return $this->successResponse($seats, 'Seats retrieved successfully');
    }


    public function byVenue($venueId): JsonResponse
    {
    $seats = Seat::where('venue_id', $venueId)->get();
    return $this->successResponse($seats, 'Venue seats retrieved successfully');
    }
}
   
   

