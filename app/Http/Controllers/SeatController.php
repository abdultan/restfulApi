<?php

namespace App\Http\Controllers;

use App\Http\Requests\SeatBlockRequest;
use Illuminate\Http\Request;
use App\Http\Requests\SeatReleaseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Seat;
<<<<<<< HEAD
=======
use App\Models\Event;
use App\Models\RezervationItem;
>>>>>>> 6291303 (ticket ve event işlemleri yapıldı)



class SeatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function block(SeatBlockRequest $request){
        $ids = $request->validated('seat_ids');
<<<<<<< HEAD
        $userId = $request->user()->id;

        return DB::transaction(function () use ($ids,$userId) {
=======
        $eventId = (int) $request->validated('event_id');
        $userId = $request->user()->id;

        // Etkinlik zamanı kontrolü (başlamamış olmalı)
        $event = Event::findOrFail($eventId);
        if (now()->greaterThanOrEqualTo($event->start_date)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Event already started',
            ], 422);
        }

        return DB::transaction(function () use ($ids,$userId,$eventId) {
>>>>>>> 6291303 (ticket ve event işlemleri yapıldı)
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
<<<<<<< HEAD
            if ($s->status !== Seat::STATUS_AVAILABLE) {
                return response()->json([
                    'status'=> 'error',
                    'message'=> "Seat {$s->id} is not available",
                ], 409);
=======
                // Global hold kontrolü (yalnızca aktif hold'lar engeller)
                if ($s->status === Seat::STATUS_RESERVED) {
                    return response()->json([
                        'status'=> 'error',
                        'message'=> "Seat {$s->id} is on hold",
                    ], 409);
                }

                // Event'e göre satılık mı? (ticket/confirmed varsa blocklanamaz)
                $isSoldForEvent = $s->tickets()
                    ->where('status','active')
                    ->whereHas('rezervation', function($q) use ($eventId){
                        $q->where('event_id',$eventId)->where('status','confirmed');
                    })
                    ->exists();
                if ($isSoldForEvent) {
                    return response()->json([
                        'status'=> 'error',
                        'message'=> "Seat {$s->id} already sold for this event",
                    ], 409);
                }

                // Event'e göre bekleyen rez var mı? (pending + expires_at>now)
                $hasPending = $s->rezervationItems()
                    ->whereHas('rezervation', function($q) use ($eventId) {
                        $q->where('event_id',$eventId)
                          ->where('status','pending')
                          ->where('expires_at','>', now());
                    })
                    ->exists();
                if ($hasPending) {
                    return response()->json([
                        'status'=> 'error',
                        'message'=> "Seat {$s->id} is reserved for this event",
                    ], 409);
>>>>>>> 6291303 (ticket ve event işlemleri yapıldı)
                }
            }

            Seat::whereIn('id', $ids)->update([
                'status'         => Seat::STATUS_RESERVED,
                'reserved_by'    => $userId,
                'reserved_until' => now()->addMinutes(15),
            ]);
            
            return response()->json([
                'status'           => 'success',
                'blocked_seat_ids' => $seats->pluck('id')->values(),
            ], 200);
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

        return response()->json([
            'status'         => 'success',
            'released_count' => $affected,
        ], 200);
    }

    public function byEvent($eventId)
    {
<<<<<<< HEAD
    $seats = Seat::whereHas('venue.events', function ($query) use ($eventId) {
        $query->where('events.id', $eventId);
    })->get();

    return response()->json($seats);
    }

=======
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

    return response()->json($seats, 200);
    }


>>>>>>> 6291303 (ticket ve event işlemleri yapıldı)
    public function byVenue($venueId)
    {
    $seats = Seat::where('venue_id', $venueId)->get();
    return response()->json($seats);
    }
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
}
