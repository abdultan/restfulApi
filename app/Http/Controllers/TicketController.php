<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketTransferRequest;
use App\Http\Requests\TicketCancelRequest;
use App\Http\Requests\TicketIndexRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Traits\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class TicketController extends Controller
{
    use ApiResponse;
    public function __construct(
        private TicketService $ticketService
    ) {}

    /**
     * Display a listing of user's tickets.
     */
    public function index(TicketIndexRequest $request): JsonResponse
    {
        $userId = $request->user()->id;
        $perPage = $request->get('per_page', 10);

        $query = Ticket::with(['seat', 'rezervation.event'])
            ->whereHas('rezervation', fn($qr) => $qr->where('user_id', $userId));

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('event_id')) {
            $query->whereHas('rezervation', fn($qr) => $qr->where('event_id', (int)$request->event_id));
        }

        $tickets = $query->latest()->paginate($perPage);

        $payload = TicketResource::collection($tickets)->response()->getData(true);

        return $this->successResponse($payload, 'Tickets retrieved successfully');
    }

    /**
     * Display the specified ticket.
     */
    public function show(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);

        $ticket->load(['seat', 'rezervation.event']);

        return $this->successResponse(new TicketResource($ticket), 'Ticket retrieved successfully');
    }

    /**
     * Transfer ticket to another user.
     */
    public function transfer(TicketTransferRequest $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('transfer', $ticket);

        try {
            $email = $request->validated('email');
            $updatedTicket = $this->ticketService->transferToEmail($ticket, $email);

            return $this->successResponse(new TicketResource($updatedTicket), 'Ticket transferred successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Cancel ticket.
     */
    public function cancel(TicketCancelRequest $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('cancel', $ticket);

        $updatedTicket = $this->ticketService->cancel($ticket);

        return $this->successResponse(new TicketResource($updatedTicket), 'Ticket cancelled successfully');
    }

    /**
     * Download ticket as PDF.
     */
    public function download(Request $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load(['seat', 'rezervation.event.venue']);

        try {
            $html = view('tickets.pdf', compact('ticket'))->render();
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4');
            return $pdf->download('ticket-' . $ticket->id . '.pdf');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'PDF generation failed: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
