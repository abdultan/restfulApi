<?php

namespace App\Http\Controllers;

use App\Http\Requests\RezervationStoreRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Seat;
use App\Models\Event;
use App\Models\Rezervation;
use App\Models\RezervationItem;
use App\Models\Ticket;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class RezervationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $rezervations = Rezervation::with(['items.seat', 'event'])
            ->where('user_id', $userId)
            ->latest()
            ->paginate(10); 

        return response()->json($rezervations, 200);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RezervationStoreRequest $request)
{
    $userId  = $request->user()->id;
    $eventId = (int) $request->validated('event_id');
    $seatIds = $request->validated('seat_ids');

    return DB::transaction(function () use ($userId, $eventId, $seatIds) {

        $seats = Seat::whereIn('id', $seatIds)->lockForUpdate()->get();

        if ($seats->count() !== count($seatIds)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Some seats not found',
            ], 404);
        }

        $venueIds = $seats->pluck('venue_id')->unique();
        if ($venueIds->count() !== 1) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Selected seats must belong to the same venue',
            ], 422);
        }

        $event = Event::find($eventId);
        if (!$event || (int)$event->venue_id !== (int)$venueIds->first()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Seats do not belong to the given event',
            ], 422);
        }

        $duplicate = RezervationItem::whereIn('seat_id', $seatIds)
            ->whereHas('rezervation', function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->where('status', 'pending');
            })
            ->first();

        if ($duplicate) {
            return response()->json([
                'status'  => 'error',
                'message' => "Seat {$duplicate->seat_id} already exists in your pending reservation",
                'reservation_id' => $duplicate->rezervation_id,
            ], 409);
        }

        foreach ($seats as $s) {
            if ($s->status !== Seat::STATUS_RESERVED) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Seat {$s->id} is not reserved",
                ], 409);
            }
            if ((int)$s->reserved_by !== (int)$userId) {   // 👈 sahiplik kontrolü
                return response()->json([
                    'status'  => 'error',
                    'message' => "Seat {$s->id} reserved by another user",
                ], 403);
            }
        
            if ($s->reserved_until && now()->greaterThan($s->reserved_until)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Seat {$s->id} hold expired",
                ], 409);
            }
        }

        $total = $seats->sum('price');

        $rez = Rezervation::create([
            'user_id'      => $userId,
            'event_id'     => $eventId,
            'status'       => 'pending', // confirm ile değişecek
            'total_amount' => $total,
            'expires_at'   => Carbon::now()->addMinutes(15),
        ]);

        foreach ($seats as $s) {
            RezervationItem::create([
                'rezervation_id' => $rez->id,
                'seat_id'        => $s->id,
                'price'          => $s->price,
            ]);
        }

        return response()->json([
            'status'          => 'success',
            'reservation_id'  => $rez->id,
            'total_amount'    => $total,
            'expires_at'      => $rez->expires_at->toISOString(),
            'items_count'     => $seats->count(),
        ], 201);
    });
}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function confirm(int $id): JsonResponse
{
    $userId = auth()->id();

    return DB::transaction(function () use ($id, $userId) {
        // 1) Rezervasyonu kilitle + temel kontroller
        $rez = Rezervation::with('items')  // RezervationItem ilişkisi olmalı
            ->lockForUpdate()
            ->find($id);

        if (!$rez || $rez->user_id !== $userId) {
            return response()->json(['message' => 'Reservation not found'], 404);
        }
        if ($rez->status !== 'pending') {
            return response()->json(['message' => 'Reservation is not pending'], 409);
        }
        if ($rez->expires_at && now()->greaterThan($rez->expires_at)) {
            return response()->json(['message' => 'Reservation expired'], 409);
        }

        // 2) İlgili seat’ları kilitle + ayrıntılı kontroller
        $seatIds = $rez->items->pluck('seat_id')->sort()->values();
        $seats   = Seat::whereIn('id', $seatIds)->lockForUpdate()->get();

        foreach ($seats as $s) {
            if ($s->status !== Seat::STATUS_RESERVED) {
                return response()->json(['message' => "Seat {$s->id} is not reserved"], 409);
            }
            if ((int)$s->reserved_by !== (int)$userId) {
                return response()->json(['message' => "Seat {$s->id} reserved by another user"], 403);
            }
            if ($s->reserved_until && now()->greaterThan($s->reserved_until)) {
                return response()->json(['message' => "Seat {$s->id} hold expired"], 409);
            }
        }

        // 3) Seats -> SOLD
        Seat::whereIn('id', $seatIds)->update([
            'status'         => Seat::STATUS_SOLD,
            'reserved_by'    => null,
            'reserved_until' => null,
        ]);

        // 4) Tickets oluştur
        $tickets = [];
        foreach ($seatIds as $sid) {
            $tickets[] = Ticket::create([
                'rezervation_id' => $rez->id,
                'seat_id'        => $sid,
                'ticket_code'    => Str::upper(Str::random(10)),
                'status'         => 'active',
            ]);
        }

        // 5) Rezervasyonu güncelle
        $rez->update(['status' => 'confirmed']);

        // 6) Yanıt
        return response()->json([
            'status'         => 'success',
            'reservation_id' => $rez->id,
            'tickets'        => collect($tickets)->map(fn($t) => [
                'id'          => $t->id,
                'seat_id'     => $t->seat_id,
                'ticket_code' => $t->ticket_code,
                'status'      => $t->status,
            ]),
        ], 200);
    });
}
public function show(Request $request, int $id): JsonResponse
{
    $userId = $request->user()->id;

    $rez = Rezervation::with(['items.seat', 'event'])
        ->where('id', $id)
        ->where('user_id', $userId)
        ->first();

    if (!$rez) {
        return response()->json(['message' => 'Reservation not found'], 404);
    }

    return response()->json($rez, 200);
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
    public function destroy(Request $request, int $id): \Illuminate\Http\JsonResponse
{
    $userId = $request->user()->id;

    $rez = Rezervation::with('items')
        ->where('id', $id)
        ->where('user_id', $userId)
        ->first();

    if (!$rez) {
        return response()->json(['message' => 'Reservation not found'], 404);
    }

    if ($rez->status !== 'pending') {
        return response()->json(['message' => 'Only pending reservations can be cancelled'], 409);
    }

    DB::transaction(function () use ($rez) {
        $seatIds = $rez->items->pluck('seat_id');

        Seat::whereIn('id', $seatIds)->update([
            'status'         => Seat::STATUS_AVAILABLE,
            'reserved_by'    => null,      // bu alanları eklediysen
            'reserved_until' => null,
        ]);

        $rez->update(['status' => 'cancelled']);
    });

    return response()->json(['status' => 'success'], 200);
}
}
