<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;

class EventController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $req): JsonResponse
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

        $payload = EventResource::collection($events)->response()->getData(true);

        return $this->successResponse($payload, 'Events retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreEventRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreEventRequest $request): JsonResponse
    {
        $event = Event::create($request->validated());
        return $this->createdResponse(new EventResource($event->load('venue')), 'Event created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  Event  $event
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Event $event): JsonResponse
    {
        return $this->successResponse(new EventResource($event->load('venue')), 'Event retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateEventRequest  $request
     * @param  Event  $event
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        $event->update($request->validated());
        return $this->successResponse(new EventResource($event->load('venue')), 'Event updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Event  $event
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Event $event): JsonResponse
    {
        if ($event->rezervations()->exists()) {
            $event->update(['status' => 'cancelled']);
            return $this->successResponse(null, 'Event cancelled (has related records)');
        }
        $event->delete();
        return $this->successResponse(null, 'Event deleted successfully');
    }
}
