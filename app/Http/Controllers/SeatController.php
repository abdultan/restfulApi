<?php

namespace App\Http\Controllers;

use App\Http\Requests\SeatBlockRequest;
use Illuminate\Http\Request;
use App\Http\Requests\SeatReleaseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\SeatService;
use App\Models\Seat;
use App\Models\Event;
use App\Models\RezervationItem;
use App\Traits\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class SeatController extends Controller
{
    use ApiResponse;
    public function __construct(private SeatService $seatService) {}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function block(SeatBlockRequest $request): JsonResponse {
        $ids = $request->validated('seat_ids');
        $eventId = (int) $request->validated('event_id');
        $userId = $request->user()->id;
        $this->authorize('block', Seat::class);

        $result = $this->seatService->block($ids, $eventId, $userId);
        if (!$result['ok']) {
            return $this->errorResponse($result['message'], $result['code']);
        }

        return $this->successResponse([
            'blocked_seat_ids' => $result['blocked_seat_ids'],
        ], 'Seats blocked successfully');
    }
    
    public function release(SeatReleaseRequest $request): JsonResponse
    {
        $ids = $request->validated('seat_ids');
        $userId = $request->user()->id;

        $this->authorize('release', Seat::class);

        $result = $this->seatService->release($ids, $userId);
        return $this->successResponse([
            'released_count' => $result['released_count']
        ], 'Seats released successfully');
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
        ->get();

    $seats = $seats->map(function ($s) {
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
   
   

