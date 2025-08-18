<?php

namespace App\Http\Controllers;

use App\Http\Requests\SeatBlockRequest;
use Illuminate\Http\Request;
use App\Http\Requests\SeatReleaseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Seat;



class SeatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function block(SeatBlockRequest $request){
        $ids = $request->validated('seat_ids');
        $userId = $request->user()->id;

        return DB::transaction(function () use ($ids,$userId) {
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
            if ($s->status !== Seat::STATUS_AVAILABLE) {
                return response()->json([
                    'status'=> 'error',
                    'message'=> "Seat {$s->id} is not available",
                ], 409);
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
    $seats = Seat::whereHas('venue.events', function ($query) use ($eventId) {
        $query->where('events.id', $eventId);
    })->get();

    return response()->json($seats);
    }

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
