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
use App\Services\RezervationService;
use App\Http\Resources\RezervationResource;
use App\Traits\ApiResponse;


class RezervationController extends Controller
{
    use ApiResponse;
    public function __construct(private RezervationService $rezervationService) {}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $rezervations = $this->rezervationService->listForUser($userId, 10);
        $payload = RezervationResource::collection($rezervations)->response()->getData(true);
        return $this->successResponse($payload, 'Reservations retrieved successfully');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RezervationStoreRequest $request): JsonResponse
    {
        $userId  = $request->user()->id;
        $eventId = (int) $request->validated('event_id');
        $seatIds = $request->validated('seat_ids');

        $this->authorize('create', Rezervation::class);

        $result = $this->rezervationService->store($userId, $eventId, $seatIds);
        if (!$result['ok']) {
            return $this->errorResponse($result['message'], $result['code'], $result);
        }

        return $this->createdResponse(new RezervationResource($result['rezervation']), 'Reservation created successfully');
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

    $rez = Rezervation::find($id);
    if (!$rez) {
        return $this->errorResponse('Reservation not found', 404);
    }
    $this->authorize('confirm', $rez);

    $result = $this->rezervationService->confirm($id, $userId);
    if (!$result['ok']) {
        return $this->errorResponse($result['message'], $result['code']);
    }

    return $this->successResponse([
        'reservation' => new RezervationResource($result['rezervation']),
        'tickets' => $result['tickets'],
    ], 'Reservation confirmed successfully');
}
public function show(Request $request, int $id): JsonResponse
{
    $userId = $request->user()->id;

    $rez = Rezervation::with(['items.seat', 'event'])
        ->where('id', $id)
        ->where('user_id', $userId)
        ->first();

    if (!$rez) {
        return $this->errorResponse('Reservation not found', 404);
    }

    return $this->successResponse(new RezervationResource($rez), 'Reservation retrieved successfully');
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
        return $this->errorResponse('Reservation not found', 404);
    }

    if ($rez->status !== 'pending') {
        return $this->errorResponse('Only pending reservations can be cancelled', 409);
    }

    $this->authorize('delete', $rez);

    $result = $this->rezervationService->cancel($id, $userId);
    if (!$result['ok']) {
        return $this->errorResponse($result['message'], $result['code']);
    }

    return $this->successResponse(null, 'Reservation cancelled successfully');
}
}
