<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $req)
    {
        $term        = $req->query('q');
        $venueId     = $req->query('venue_id');
        $status      = $req->query('status');
        $from        = $req->query('from');
        $to          = $req->query('to');
        $onlyUpcoming= $req->boolean('upcoming');

        $events = Event::with('venue')
            ->when($status, fn($q)=>$q->where('status',$status))
            ->when(!$status, fn($q)=>$q->where('status','published'))
            ->byVenue($venueId)
            ->search($term)
            ->when($onlyUpcoming, fn($q)=>$q->upcoming())
            ->when($from && $to, fn($q)=>$q->between($from,$to))
            ->orderBy('start_date')
            ->paginate(10);

        return response()->json($events);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreEventRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreEventRequest $request)
    {
        $event = Event::create($request->validated());
        return response()->json($event->load('venue'), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Event  $event
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Event $event)
    {
        return response()->json($event->load('venue'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateEventRequest  $request
     * @param  Event  $event
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        $event->update($request->validated());
        return response()->json($event->load('venue'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Event  $event
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Event $event)
    {
        if ($event->rezervations()->exists()) {
            $event->update(['status' => 'cancelled']);
            return response()->json(['message'=>'Event cancelled (has related records).']);
        }
        $event->delete();
        return response()->json(['message'=>'Event deleted.']);
    }
}
