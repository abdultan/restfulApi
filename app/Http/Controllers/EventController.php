<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
=======
use App\Models\Event;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
>>>>>>> 6291303 (ticket ve event işlemleri yapıldı)
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
<<<<<<< HEAD
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
=======
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
>>>>>>> 6291303 (ticket ve event işlemleri yapıldı)
    }

    /**
     * Store a newly created resource in storage.
     *
<<<<<<< HEAD
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
=======
     * @param  StoreEventRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreEventRequest $request)
    {
        $event = Event::create($request->validated());
        return response()->json($event->load('venue'), 201);
>>>>>>> 6291303 (ticket ve event işlemleri yapıldı)
    }

    /**
     * Display the specified resource.
     *
<<<<<<< HEAD
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
=======
     * @param  Event  $event
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Event $event)
    {
        return response()->json($event->load('venue'));
>>>>>>> 6291303 (ticket ve event işlemleri yapıldı)
    }

    /**
     * Update the specified resource in storage.
     *
<<<<<<< HEAD
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
=======
     * @param  UpdateEventRequest  $request
     * @param  Event  $event
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        $event->update($request->validated());
        return response()->json($event->load('venue'));
>>>>>>> 6291303 (ticket ve event işlemleri yapıldı)
    }

    /**
     * Remove the specified resource from storage.
     *
<<<<<<< HEAD
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
=======
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
>>>>>>> 6291303 (ticket ve event işlemleri yapıldı)
    }
}
